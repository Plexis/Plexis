<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Library/Auth.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Auth
 * @contains    InvalidUsernameException
 * @contains    InvalidPasswordException
 * @contains    InvalidEmailException
 * @contains    AccountBannedException
 * @contains    IpBannedException
 */
namespace Library;

// Bring some classes into scope
use \Core\Database;
use \Core\EventHandler;
use \Core\Request;
use \Exception;
use \Plexis;

/**
 * Authorization and User class.
 *
 * This class is used to proccess user's, and thier permissions
 *
 * @author      Steven Wilson 
 * @package     Library
 */
class Auth
{
    /**
     * Session started? Used internally
     * @var bool
     */
    protected static $started = false;
    
    /**
     * When the sessoin expires
     * @var int
     */
    protected static $expireTime;

    /**
     * Database Driver object for the Plexis database
     * @var \Database\Driver
     */
    protected static $DB;
    
    /**
     * The Realm object from the wowlib
     * @var \Wowlib\Realm
     */
    protected static $realm;

    /**
     * The sessions id
     * @var string
     */
    protected static $sessionid = 0;
    
    /**
     * Current users access permissions
     * @var int[]
     */
    protected static $permissions;
    
    /**
     * Users data array
     * @var mixed[]
     */
    protected static $data = array(
        'logged_in' => false,
        'id' => 0,
        'username' => 'Guest',
    );
    
    /**
     * Contructor method (called internally)
     *
     * Initiates the user sessions and such
     *
     * @return void
     */
    public static function Init()
    {
        // Add trace for debugging
        // //\Debug::trace('Initializing User class...', __FILE__, __LINE__);
        
        // Start the session
        if(!self::$started)
        {
            session_start();
            self::$started = true;
        }
        
        // Setup the DB connections, and get users real IP address
        self::$DB = Database::GetConnection('DB');
        
        // Load the emulator (realm)
        self::$realm = Plexis::GetRealm();
        
        // Set our session expire time
        self::$expireTime = (60 * 60 * 24 * 30);
        
        // Load this users credentials
        self::StartSession();
        
        // Add trace for debugging
        // //\Debug::trace('User class initialized successfully', __FILE__, __LINE__);
    }
    
    /**
     * Internal method used to check to if the user is logged in by session.
     * If not then a username, id, and account level are set at guest.
     * Also checks for login expire time.
     *
     * @return void
     */
    protected static function StartSession()
    {
        // Check for a session cookie
        $cookie = Request::Cookie('session', false);
        
        // If the cookie doesnt exists, then neither does the session
        if($cookie == false) goto Guest;
        
        // Read cookie data to get our token
        $cookie = base64_decode( $cookie );
        if(strpos($cookie, '::') != false):
            list($userid, $token) = explode('::', $cookie);
            $userid = (int) $userid;
        else:
            self::Logout(false);
            goto Guest;
        endif;

        // Get the database result
        $query = "SELECT * FROM `pcms_sessions` WHERE `token` = ?";
        $session = self::$DB->query( $query, array($token) )->fetchRow();
        
        // Unserialize the user_data array
        if(is_array($session))
        {
            // check users IP address to prevent cookie stealing
            if( $session['ip_address'] != Request::ClientIp() )
            {
                // Session time is expired
                // //\Debug::trace('User IP address doesnt match the IP address of the session id. Forced logout', __FILE__, __LINE__);
                self::Logout(false);
            }
            elseif($session['expire_time'] < (time() - self::$expireTime))
            {
                // Session time is expired
                // //\Debug::trace('User session expired, Forced logout', __FILE__, __LINE__);
                self::Logout(false);
            }
            else
            {
                // User is good and logged in
                self::$data['logged_in'] = true;
                self::$sessionid = $session['token'];
            }
        }
        
        // if the Session isnt set or is false
        if(!self::$data['logged_in']) 
        {
            Guest:
            {
                // Add trace for debugging
                // //\Debug::trace('Loading user as guest', __FILE__, __LINE__);
        
                // Get guest privilages
                $query = "SELECT * FROM `pcms_account_groups` WHERE `group_id`=1";
                
                // Query our database set default guest information
                $result = self::$DB->query( $query )->fetchRow();			
                
                // Load our perms into a different var and unset
                $perms = unserialize( $result['permissions'] );
                unset( $result['permissions'] );
                
                // Merge and set the data
                self::$data = array_merge(array(
                    'logged_in' => false,
                    'id' => 0,
                    'username' => 'Guest'
                ), $result);
                
                // Load the permissions
                self::LoadPermissions( $result['group_id'], $perms );
            }
        }
        
        // Everything is good, user is valid, but we need to load his information
        else
        {
            if(!self::_initUser($userid)) goto Guest;
        }
    }
    
    /**
     * Method used to proccess a user login
     *
     * @param string $username The username to proccess
     * @param string $password Unencrypted password to the account
     *
     * @throws InvalidUsernameException if the account doesnt exist
     * @throws InvalidPasswordException if the account password is incorrect
     * @throws AccountBannedException if the account is banned
     *
     * @return bool Return true if the user is logged in, false otherwise
     */
    public static function Login($username, $password)
    {
        // Remove white space in front and behind
        $username = trim($username);
        $password = trim($password);

        // if the username or password is empty, return false
        if(empty($username) || empty($password))
        {
            Template::Message('error', 'login_failed_field_invalid');
            return false;
        }
        
        // Add trace for debugging
        ////\Debug::trace("User {$username} logging in...", __FILE__, __LINE__);
        
        // If the Emulator cant match the passwords, or user doesnt exist,
        // Then we spit out an error and return false
        if(!self::$realm->validate($username, $password))
        {
            // Add trace for debugging
            // //\Debug::trace("Failed to validate password for account '{$username}'. Login failed", __FILE__, __LINE__);
            Template::Message('error', 'login_failed_wrong_credentials');
            return false;
        }
        
        // Username exists and password is correct, Lets log in
        else
        {
            // Fetch account
            if(!self::_initUser($username)) return false;
            
            // Generate a completely random session id
            $time = microtime(1);
            $string = sha1(base64_encode(md5(utf8_encode( $time ))));
            self::$sessionid = substr($string, 0, 20);
            
            // Set additionals, and return true
            $time = time();
            $data = array(
                'token' => self::$sessionid,
                'ip_address' => Request::ClientIp(),
                'expire_time' => ($time + self::$expireTime)
            );
            
            // Insert session information
            self::$DB->insert('pcms_sessions', $data);

            // Update user with new session id
            self::$DB->update('pcms_accounts', array('last_seen' => date('Y-m-d H:i:s', $time)), "`id`=". self::$data['id']);
            
            // Set cookie
            $token = base64_encode(self::$data['id'] .'::'. self::$sessionid);
            Response::SetCookie('session', $token, (time() + self::$expireTime));
            
            // Add trace for debugging
            ////\Debug::trace("Account '{$username}' logged in successfully", __FILE__, __LINE__);
            
            // Fire the login event
            EventHandler::Trigger('user_logged_in', array(self::$data['id'], $username));
            
            // Return
            return TRUE;
        }
    }
    
    /**
     * Method used to create a new account
     *
     * @param string $username The account username to create
     * @param string $password Unencrypted password to the account
     * @param string $email New accounts email address
     * @param int $sq The secret question ID. Leave null for no secrect question
     * @param string $sa The secret question answer. Leave null for no secrect question
     *
     * @throws InvalidUsernameException if the username is invalid (too long or short)
     * @throws InvalidPasswordException if the password is invalid (too long or short)
     * @throws InvalidEmailException if the email is not a real email
     * @throws AccountExistsException if the account name is already taken
     * @throws IpBannedException if the ip address is banned
     *
     * @return int The account ID upon success, false otherwise
     */
    public static function Register($username, $password, $email, $sq = NULL, $sa = NULL)
    {
        // Remove white space in front and behind
        $username = trim(ucfirst(strtolower($username)));
        $password = trim($password);
        $email = trim($email);

        // If the username, password, or email is empty, return false
        if(empty($username) || empty($password) || empty($email))
        {
            Template::Message('error', 'reg_failed_field_invalid');
            return false;
        }
        
        // Add trace for debugging
        ////\Debug::trace("Registering account '{$username}'...", __FILE__, __LINE__);
        
        // Make sure the users IP isnt blocked
        if(self::$realm->ipBanned( self::$data['ip_address'] ))
        {
            throw new IpBannedException('');
        }
        
        // If the result is not was false, then the username already exists
        if(self::$realm->accountExists($username))
        {
            // Add trace for debugging
            ////\Debug::trace("Account '{$username}' already exists. Registration failed", __FILE__, __LINE__);
            throw new AccountExistsException('');
        }
        
        // We are good to go, register the user
        else
        {
            // Try and create the account through the emulator class
            $id = self::$realm->createAccount($username, $password, $email, self::$data['ip_address']);
            
            // If insert into Realm Database is a success, move on
            if($id !== false)
            {
                // Add trace for debugging
                //\Debug::trace("Account '{$username}' created successfully", __FILE__, __LINE__);
                
                // Defaults
                $activated = 1;
                $secret = NULL;
                
                // Process account verification
                if( config('reg_email_verification') )
                {
                    $User = self::$realm->fetchAccount($id);
                    $User->setLocked(true);
                    $User->save();
                    $activated = 0;
                }
                
                // Secret question / answer processing
                if($sq != NULL && $sa != NULL)
                {
                    $array = array(
                        'id' => $sq,
                        'answer' => trim($sa),
                        'email' => $email
                    );
                    $secret = base64_encode( serialize($array) );
                }
                
                // Create our data array
                $data = array(
                    'id' => $id,
                    'username' => $username,
                    'email' => $email,
                    'activated' => $activated,
                    'registration_ip' => self::$data['ip_address'],
                    '_account_recovery' => $secret
                );
                
                // Try and insert into pcms_accounts table
                self::$DB->insert('pcms_accounts', $data);
                
                // Fire the registration event
                $event = array($id, $username, $password, $email, self::$data['ip_address']);
                EventHandler::Trigger('account_created', $event);
                
                // Return ID
                return $id;
            }
            return false;
        }
    }
    
    /**
     * Loads the permissions specific to this user
     *
     * @param int $gid The group id
     * @param int[] $perms The list of all permissions for the usergroup
     *
     * @return void
     */
    protected static function LoadPermissions($gid, $perms)
    {
        // Add trace for debugging
        //\Debug::trace('Loading permissions for group id: '. $gid, __FILE__, __LINE__);
        
        // set to empty array if false, else we need the keys for comparison
        $perms = ($perms == false) ? array() : array_keys($perms);
        
        // Get alist of all permissions
        $query = "SELECT `key` FROM `pcms_permissions`";
        $list = self::$DB->query( $query )->fetchAll( \PDO::FETCH_COLUMN );
        
        // Unset old perms that dont exist anymore
        $dif = array_diff($perms, $list);
        $perms = array_intersect($perms, $list);
        
        // Build a list of current permissions
        $p = array();
        foreach($perms as $perm) $p[$perm] = 1;
        
        // Update the DB if there are any changes
        if(!empty($dif))
        {
            self::$DB->update('pcms_account_groups', array('permissions' => serialize( $p )), "`group_id`=".$gid);
        }
        
        // Set this users permissions
        self::$permissions = $p;
    }
    
    /**
     * Used to find if user has a specified permission
     *
     * @param string $key Permission name
     *
     * @return bool Returns true if the user has permissions, false otherwise
     */
    public static function HasPermission($key)
    {
        // Super admin always wins
        if(self::$data['is_super_admin']) return true;
        
        // Not a super admin, continue
        return (bool) (array_key_exists($key, self::$permissions)) ? self::$permissions[$key] : false;
    }
    
    /**
     * Logs the user out and sets all session variables to Guest.
     *
     * @param bool $newSession Start a new session? Should only
     * be set internally in this class.
     *
     * @return void
     */
    public static function Logout($newSession = true)
    {
        // Make sure we are logged in first!
        if(!self::$data['logged_in']) return;
        
        // Unset cookie
        Response::SetCookie('session', 0, (time() - 1));
        $_COOKIE['session'] = false;
        
        // remove session from database
        self::$DB->delete('pcms_sessions', "`token`='{$this->sessionid}'");
        
        // Add trace for debugging
        //\Debug::trace("Logout request recieved for account '{self::$data['username']}'", __FILE__, __LINE__);
        
        // Fire the login event
        EventHandler::Trigger('user_logged_out', array(self::$data['id'], self::$data['username']));
        
        // Init a new session
        if($newSession == true) self::StartSession();
    }
    
    /**
     * This method is used to initiate a user when an ID or username is determined
     *
     * @param int $userid The account id
     *
     * @return bool
     */
    protected static function _initUser($userid)
    {
        // Fetch account
        $Account = self::$realm->fetchAccount($userid);
        if(!is_object($Account))
        {
            // Add trace for debugging
            //\Debug::trace("Account id {$userid} doesnt exist in the realm database. Failed to init user account", __FILE__, __LINE__);
            return false;
        }
        
        // Build our rediculas query
        $query = "SELECT 
                `activated`, 
                `pcms_accounts`.`group_id`, 
                `last_seen`, 
                `registered`, 
                `registration_ip`, 
                `language`, 
                `selected_theme`, 
                `votes`, 
                `vote_points`, 
                `vote_points_earned`, 
                `vote_points_spent`, 
                `donations`, 
                `_account_recovery`,
                `pcms_account_groups`.`title`,
                `pcms_account_groups`.`is_banned`,
                `pcms_account_groups`.`is_user`,
                `pcms_account_groups`.`is_admin`,
                `pcms_account_groups`.`is_super_admin`,
                `pcms_account_groups`.`permissions`
            FROM `pcms_accounts` INNER JOIN `pcms_account_groups` ON 
            pcms_accounts.group_id = pcms_account_groups.group_id WHERE `id` = ?";
        
        // Query our database and get the users information
        $result = self::$DB->query( $query, array($Account->getId()), false )->fetchRow();
        
        // If the user doesnt exists in the table, we need to insert it
        if($result === false)
        {
            // Add trace for debugging
            //\Debug::trace("User account '{$Account->getUsername()}' doesnt exist in Plexis database, fetching account from realm", __FILE__, __LINE__);
            $data = array(
                'id' => $Account->getId(), 
                'username' => ucfirst(strtolower($Account->getUsername())), 
                'email' => $Account->getEmail(), 
                'activated' => 1,
                'registered' => ($Account->joinDate() == false) ? date("Y-m-d H:i:s", time()) : $Account->joinDate(),
                'registration_ip' => Request::ClientIp()
            );
            self::$DB->insert( 'pcms_accounts', $data );
            $result = self::$DB->query( $query )->fetchRow();
            
            // If the insert failed, we have a fatal error
            if($result === false)
            {
                // Add trace for debugging
                //\Debug::trace("There was a fatal error trying to insert account data into the plexis database", __FILE__, __LINE__);
                show_error('fatal_error', false, E_ERROR);
                return false;
            }
        }
        
        // Load our perms into a different var and unset
        $perms = unserialize( $result['permissions'] );
        unset( $result['permissions'] );
        
        // Make sure we have access to our account, we have to do this after saving the session unfortunatly
        if( (!isset($perms['account_access']) || $perms['account_access'] == 0) && $result['is_super_admin'] == 0)
        {
            // Add trace for debugging
            //\Debug::trace("User has no permission to access account. Login failed.", __FILE__, __LINE__);
            Template::Message('warning', 'account_access_denied');
            return false;
        }
        
        // We are good, save permissions for this user
        self::LoadPermissions($result['group_id'], $perms);
        
        // Make sure the account isnt locked due to verification
        if($result['activated'] == false && config('reg_email_verification') == TRUE)
        {
            // Add trace for debugging
            //\Debug::trace("Account '{$username}' is unactivated. Login failed.", __FILE__, __LINE__);
            Template::Message('warning', 'login_failed_account_unactivated');
            return false;
        }
        
        // Custom variable for QA checking
        $result['_account_recovery'] = ($result['_account_recovery'] != null && strlen($result['_account_recovery']) > 10);
        
        // Set our users info up the the session and carry onwards :D
        self::$data = array_merge( array(
            'logged_in' => true,
            'id' => $Account->getId(), 
            'username' => ucfirst( strtolower($Account->getUsername()) ),
            'email' => $Account->getEmail(),
            'ip_address' => self::$data['ip_address']
        ), $result);
        
        // Add trace for debugging
        //\Debug::trace('Loaded user '. $Account->getUsername(), __FILE__, __LINE__);
        return true;
    }
}

// Class exceptions

/**
 * Thrown by the Auth Class when the provided username is invalid in format (Too long, Too short)
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/Auth.php
 * @see         Auth
 */
class InvalidUsernameException extends Exception {}

/**
 * Thrown by the Auth Class when the provided password is invalid in format (Too long, Too short)
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/Auth.php
 * @see         Auth
 */
class InvalidPasswordException extends Exception {}

/**
 * Thrown by the Auth Class when the provided email is invalid.
 * @package Library
 * @subpackage Exceptions
 * @see Auth
 */
class InvalidEmailException extends Exception {}

/**
 * Thrown by the Auth Class during the Register method, if the account name provided already exists
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/Auth.php
 * @see         Auth::Register()
 */
class AccountExistsException extends Exception {}

/**
 * Thrown by the Auth Class when logging in, and the account name is banned
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/Auth.php
 * @see         Auth::Login()
 */
class AccountBannedException extends Exception {}

/**
 * Thrown by the Auth Class when registering an account, and the Remote IP is banned.
 * @package     Library
 * @subpackage  Exceptions
 * @file        System/Library/Auth.php
 * @see         Auth::Register()
 */
class IpBannedException extends Exception {}
// EOF