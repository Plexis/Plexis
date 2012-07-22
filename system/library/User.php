<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Auth()
| ---------------------------------------------------------------
|
| This class sets up the user, and processes permissions, logins,
| logouts, and registration.
|
*/
namespace Library;

class User
{
    // Session started?
    protected static $started = false;
    
    // The loader class
    protected $load;

    // When the sessoin expires
    protected $expire_time;

    // The databases and realm
    protected $DB;
    protected $realm;

    // The session id
    protected $sessionid = 0;
    
    // Users access permission
    protected $permissions;
    
    // Clients IP address
    protected $data = array(
        'username' => 'Guest',
        'ip_address' => '0.0.0.0',
        'logged_in' => false,
    );

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
| Initiates the user sessions and such
|
*/

    public function __construct()
    {
        // Add trace for debugging
        \Debug::trace('Initializing User class...', __FILE__, __LINE__);
        
        // Start the session
        if(!self::$started)
        {
            session_start();
            self::$started = true;
        }
        
        // Init the loader
        $this->load = load_class('Loader');
        
        // Setup the DB connections, and get users real IP address
        $this->DB = $this->load->database('DB');
        $this->input = load_class('Input');
        $this->data['ip_address'] = $this->input->ip_address();
        
        // Load the emulator (realm)
        $this->realm = $this->load->realm();
        
        // Set our session expire time
        $this->expire_time = (60 * 60 * 24 * 30);
        
        // Load this users credentials
        $this->Init();
        
        // Add trace for debugging
        \Debug::trace('User class initialized successfully', __FILE__, __LINE__);
    }

/*
| ---------------------------------------------------------------
| Method: Init()
| ---------------------------------------------------------------
|
| This method checks to see if the user is logged in by session.
| If not then a username, id, and account level are set at guest.
| Also checks for login expire time.
|
*/

    protected function Init()
    {
        // Check for a session cookie
        $cookie = $this->input->cookie('session');
        
        // If the cookie doesnt exists, then neither does the session
        if($cookie == false) goto Guest;
        
        // Read cookie data to get our token
        $cookie = base64_decode( $cookie );
        if(strpos($cookie, '::') != false):
            list($userid, $token) = explode('::', $cookie);
        else:
            $this->logout(false);
            goto Guest;
        endif;

        // Get the database result
        $query = "SELECT * FROM `pcms_sessions` WHERE `token` = ?";
        $session = $this->DB->query( $query, array($token) )->fetch_row();
        
        // Unserialize the user_data array
        if($session !== false)
        {
            // Compatability till next update
            if(!isset($session['expire_time'])) goto Guest;
            
            // check users IP address to prevent cookie stealing
            if( $session['ip_address'] != $this->data['ip_address'] )
            {
                // Session time is expired
                \Debug::trace('User IP address doesnt match the IP address of the session id. Forced logout', __FILE__, __LINE__);
                $this->logout(false);
            }
            elseif($session['expire_time'] < (time() - $this->expire_time))
            {
                // Session time is expired
                \Debug::trace('User session expired, Forced logout', __FILE__, __LINE__);
                $this->logout(false);
            }
            else
            {
                // User is good and logged in
                $this->data['logged_in'] = true;
                $this->sessionid = $session['token'];
            }
        }
        
        // if the Session isnt set or is false
        if(!$this->data['logged_in']) 
        {
            Guest:
            {
                // Add trace for debugging
                \Debug::trace('Loading user as guest', __FILE__, __LINE__);
        
                // Get guest privilages
                $query = "SELECT * FROM `pcms_account_groups` WHERE `group_id`=1";
                
                // Query our database set default guest information
                $result = $this->DB->query( $query )->fetch_row();			
                $result['username'] = "Guest";
                $result['logged_in'] = false;
                
                // Load our perms into a different var and unset
                $perms = unserialize( $result['permissions'] );
                unset( $result['permissions'] );
                
                // Set Guest Data
                $result['username'] = 'Guest';
                $result['logged_in'] = false;
                
                // Merge and set the data
                $this->data = array_merge($this->data, $result);
            }
        }
        
        // Everything is good, user is valid, but we need to load his information
        else
        {
            // Build our query
            $query = "SELECT * FROM `pcms_accounts` 
                INNER JOIN `pcms_account_groups` ON 
                pcms_accounts.group_id = pcms_account_groups.group_id 
                WHERE `id` = '". $userid ."'";
            
            // Query our database and get the users information
            $result = $this->DB->query( $query )->fetch_row();
            
            // Make sure user wasnt deleted!
            if($result == false) goto Guest;
            
            // Good to go, Update the last seen if its more then 5 minutes ago
            if((strtotime($result['last_seen']) + 300) < time())
            {
                $this->DB->update('pcms_accounts', array('last_seen' => date('Y-m-d H:i:s', time())), "`id`=". $userid);
            }
            
            // Load our perms into a different var and unset
            $perms = unserialize( $result['permissions'] );
            unset( $result['permissions'] );
            
            // Custom variable for QA checking
            ($result['_account_recovery'] == NULL) ? $set = false : $set = TRUE;
          
            // Loop through and remove private columns (underscore as first character)
            foreach($result as $key => $value)
            {
                if(!strncmp($key, "_", 1))
                {
                    unset($result[$key]);
                }
            }
            
            // Add back our account recovery stuff
            $result['_account_recovery'] = $set;
            
            // Set our users info up the the session and carry onwards :D
            $this->data = array_merge($this->data, $result);
            
            // Add trace for debugging
            \Debug::trace('Loaded user '. $result['username'], __FILE__, __LINE__);
        }
        
        // Load the permissions
        $this->load_permissions( $result['group_id'], $perms );
    }

/*
| ---------------------------------------------------------------
| Method: login()
| ---------------------------------------------------------------
|
| The main login script!
|
| @Param: (String) $username - The username logging in
| @Param: (String) $password - The unencrypted password
| @Return (Bool) True upon success, false otherwise
|
*/

    public function login($username, $password)
    {
        // Remove white space in front and behind
        $username = trim($username);
        $password = trim($password);

        // if the username or password is empty, return false
        if(empty($username) || empty($password))
        {
            output_message('error', 'login_failed_field_invalid');
            return false;
        }
        
        // Add trace for debugging
        \Debug::trace("User {$username} logging in...", __FILE__, __LINE__);
        
        // If the Emulator cant match the passwords, or user doesnt exist,
        // Then we spit out an error and return false
        $account_id = $this->realm->validate($username, $password);
        if($account_id === false)
        {
            // Add trace for debugging
            \Debug::trace("Failed to validate password for account '{$username}'. Login failed", __FILE__, __LINE__);
            output_message('error', 'login_failed_wrong_credentials');
            return false;
        }
        
        // Username exists and password is correct, Lets log in
        else
        {
            // Build our query	
            $query = "SELECT * FROM `pcms_accounts` 
                INNER JOIN `pcms_account_groups` ON 
                pcms_accounts.group_id = pcms_account_groups.group_id 
                WHERE id = ?";
            
            // Query our database and get the users information
            $result = $this->DB->query( $query, array($account_id) )->fetch_row();
            
            // If the user doesnt exists in the table, we need to insert it
            if($result === false)
            {
                // Add trace for debugging
                \Debug::trace("User account '{$username}' doesnt exist in Plexis database, fetching account from realm", __FILE__, __LINE__);
                $Account = $this->realm->fetchAccount($account_id);
                $data = array(
                    'id' => $account_id, 
                    'username' => ucfirst(strtolower($username)), 
                    'email' => $Account->getEmail(), 
                    'activated' => 1,
                    'registered' => ($Account->joinDate() == false) ? date("Y-m-d H:i:s", time()) : $Account->joinDate(),
                    'registration_ip' => $this->data['ip_address']
                );
                $this->DB->insert( 'pcms_accounts', $data );
                $result = $this->DB->query( $query )->fetch_row();
                
                // If the insert failed, we have a fatal error
                if($result === false)
                {
                    // Add trace for debugging
                    \Debug::trace("There was a fatal error trying to insert account data into the plexis database", __FILE__, __LINE__);
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
                \Debug::trace("User has no permission to access account. Login failed.", __FILE__, __LINE__);
                output_message('warning', 'account_access_denied');
                return false;
            }
            
            // We are good, save permissions for this user
            $this->load_permissions($result['group_id'], $perms);
            
            // Make sure the account isnt locked due to verification
            if($result['activated'] == false && config('reg_email_verification') == TRUE)
            {
                // Add trace for debugging
                \Debug::trace("Account '{$username}' is unactivated. Login failed.", __FILE__, __LINE__);
                output_message('warning', 'login_failed_account_unactivated');
                return false;
            }
            
            // Generate a completely random session id
            $time = microtime(1);
            $string = sha1(base64_encode(md5(utf8_encode( $time ))));
            $this->sessionid = substr($string, 0, 20);
            
            // Set additionals, and return true
            $time = time();
            $data = array(
                'token' => $this->sessionid,
                'ip_address' => $this->data['ip_address'],
                'expire_time' => ($time + $this->expire_time)
            );
            
            // Insert session information
            $this->DB->insert('pcms_sessions', $data);

            // Update user with new session id
            $this->DB->update('pcms_accounts', array('last_seen' => date('Y-m-d H:i:s', $time)), "`id`=". $result['id']);
            
            // Custom variable for QA checking
            ($result['_account_recovery'] == NULL) ? $set = false : $set = TRUE;
          
            // Loop through and remove private columns (underscore as first character)
            foreach($result as $key => $value)
            {
                if(!strncmp($key, "_", 1))
                {
                    unset($result[$key]);
                }
            }
            
            // Add back our account recovery stuff
            $result['_account_recovery'] = $set;
            
            // Set our users info up the the session and carry onwards :D
            $this->data = array_merge($this->data, $result);
            
            // Set cookie
            $token = base64_encode($this->data['id'] .'::'. $this->sessionid);
            $this->input->set_cookie('session', $token, (time() + $this->expire_time));
            
            // Add trace for debugging
            \Debug::trace("Account '{$username}' logged in successfully", __FILE__, __LINE__);
            
            // Fire the login event
            load_class('Events')->trigger('user_logged_in', array($this->data['id'], $username));
            
            // Return
            return TRUE;
        }
    }

/*
| ---------------------------------------------------------------
| Method: register()
| ---------------------------------------------------------------
|
| The main register script
|
| @Param: (String) $username - The username logging in
| @Param: (String) $password - The unencrypted password
| @Param: (String) $email - The email
| @Param: (Int) $sq - The secret Question ID
| @Param: (String) $sa - The secret Question answer
| @Return (Int) Account ID upon success, false otherwise
|
*/

    public function register($username, $password, $email, $sq = NULL, $sa = NULL)
    {
        // Remove white space in front and behind
        $username = trim(ucfirst(strtolower($username)));
        $password = trim($password);
        $email = trim($email);

        // If the username, password, or email is empty, return false
        if(empty($username) || empty($password) || empty($email))
        {
            output_message('error', 'reg_failed_field_invalid');
            return false;
        }
        
        // Add trace for debugging
        \Debug::trace("Registering account '{$username}'...", __FILE__, __LINE__);
        
        // Make sure the users IP isnt blocked
        if($this->realm->ipBanned( $this->data['ip_address'] ) == TRUE)
        {
            // Add trace for debugging
            \Debug::trace("Ip address is banned. Registration failed", __FILE__, __LINE__);
            output_message('error', 'reg_failed_ip_banned');
            return false;
        }
        
        // If the result is not was false, then the username already exists
        if($this->realm->accountExists($username))
        {
            // Add trace for debugging
            \Debug::trace("Account '{$username}' already exists. Registration failed", __FILE__, __LINE__);
            output_message('error', 'reg_failed_username_exists');
            return false;
        }
        
        // We are good to go, register the user
        else
        {
            // Try and create the account through the emulator class
            $id = $this->realm->createAccount($username, $password, $email, $this->data['ip_address']);
            
            // If insert into Realm Database is a success, move on
            if($id !== false)
            {
                // Add trace for debugging
                \Debug::trace("Account '{$username}' created successfully", __FILE__, __LINE__);
                
                // Defaults
                $activated = 1;
                $secret = NULL;
                
                // Process account verification
                if( config('reg_email_verification') )
                {
                    $User = $this->realm->fetchAccount($id);
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
                    'registration_ip' => $this->data['ip_address'],
                    '_account_recovery' => $secret
                );
                
                // Try and insert into pcms_accounts table
                $this->DB->insert('pcms_accounts', $data);
                
                // Fire the registration event
                $event = array($id, $username, $password, $email, $this->data['ip_address']);
                load_class('Events')->trigger('account_created', $event);
                
                // Return ID
                return $id;
            }
            return false;
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: load_permissions()
| ---------------------------------------------------------------
|
| Loads the permissions specific to this user
|
| @Return (None)
|
*/

    protected function load_permissions($gid, $perms)
    {
        // Add trace for debugging
        \Debug::trace('Loading permissions for group id: '. $gid, __FILE__, __LINE__);
        
        // set to empty array if false
        if($perms == false) $perms = array();
        
        // Get alist of all permissions
        $query = "SELECT `key` FROM `pcms_permissions`";
        $r = $this->DB->query( $query )->fetch_array();
        
        // Fix array keys
        foreach($r as $p)
        {
            $list[ $p['key'] ] = $p;
        }
        unset($r);
        
        // Unset old perms that dont exist anymore
        $dif = false;
        foreach($perms as $key => $value)
        {
            if( !isset($list[ $key ]) )
            {
                $dif = TRUE;
                unset($perms[ $key ]);
            }
        }
        
        // Update the DB if there are any changes
        if($dif)
        {
            $p = serialize($perms);
            $this->DB->update('pcms_account_groups', array('permissions' => $p), "`group_id`=".$gid);
        }
        
        // Set this users permissions
        $this->permissions = $perms;
    }
    
/*
| ---------------------------------------------------------------
| Method: has_permissions()
| ---------------------------------------------------------------
|
| Used to find if user has a specified permission
|
| @Return (Int) 1 if the user has permission, else 0
|
*/

    public function has_permission($key)
    {
        // Super admin always wins
        if($this->data['is_super_admin']) return 1;
        
        // Not a super admin, continue
        if(array_key_exists($key, $this->permissions))
        {
            return $this->permissions[$key];
        }
        return 0;
    }

/*
| ---------------------------------------------------------------
| Method: logout()
| ---------------------------------------------------------------
|
| Logs the user out and sets all session variables to Guest.
|
| @Param (Bool) $newSession - Init a new session? Should only
|   be set internally in this class.
| @Return (None)
|
*/

    public function logout($newSession = true)
    {
        // Make sure we are logged in first!
        if(!$this->data['logged_in']) return;
        
        // Unset cookie
        $this->input->set_cookie('session', 0, (time() - 1));
        $_COOKIE['session'] = false;
        
        // remove session from database
        $this->DB->delete('pcms_sessions', "`token`='{$this->sessionid}'");
        
        // Add trace for debugging
        \Debug::trace("Logout request recieved for account '{$this->data['username']}'", __FILE__, __LINE__);
        
        // Fire the login event
        load_class('Events')->trigger('user_logged_out', array($this->data['id'], $this->data['username']));
        
        // Init a new session
        if($newSession == true) $this->Init();
    }
    
/*
| ---------------------------------------------------------------
| Method: __get()
| ---------------------------------------------------------------
|
| This magic method is used to return the requested user variable
| such as 'username' when a function of that name is called.
|
| @Return (Mixed) - Returns user value, or false if it doesnt exist
|
*/

    public function __get($var)
    {
        // If passed function name is just 'data', then return all user data
        if($var == 'data')
            return $this->data;
        else
            return (array_key_exists($var, $this->data)) ? $this->data[$var] : false;
    }
}
// EOF