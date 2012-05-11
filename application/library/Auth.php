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
namespace Application\Library;

class Auth
{
    // The loader class
    protected $load;

    // When the sessoin expires
    protected $expire_time;

    // The databases and realm
    protected $DB, $RDB, $realm;

    // The session class
    protected $session;
    
    // Users access permission
    protected $permissions;
    
    // Clients IP address
    public $remote_ip;

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
| Initiates the user sessions and such
|
*/

    public function __construct()
    {
        // Init the loader
        $this->load = load_class('Loader');
        
        // Setup the DB connections
        $this->DB = $this->load->database('DB');
        $this->RDB = $this->load->database('RDB');
        
        // Load the session class
        $this->session = $this->load->library('Session');
        
        // Load the Input class and get our users IP
        $this->input = load_class('Input');
        $this->remote_ip = $this->input->ip_address();
        
        // Load the emulator
        $this->realm = $this->load->realm();
        
        // Set our session expire time
        $this->expire_time = (60 * 60 * 24 * 30);
        
        // Load this users credentials
        $this->load_user();
    }

/*
| ---------------------------------------------------------------
| Function: load_user()
| ---------------------------------------------------------------
|
| This method checks to see if the user is logged in by session.
| If not then a username, id, and account level are set at guest.
| Also checks for login expire time.
|
*/

    protected function load_user()
    {
        // Get our session information
        $session = $this->session->get('user');
        
        // if the Session isnt set or is false
        if( $session === NULL || $session['logged_in'] == FALSE ) 
        {
            Guest:
            {
                // Get guest privilages
                $query = "SELECT * FROM `pcms_account_groups` WHERE `group_id`=1";
                
                // Query our database set default guest information
                $result = $this->DB->query( $query )->fetch_row();			
                $result['username'] = "Guest";
                $result['logged_in'] = FALSE;
                
                // Load our perms into a different var and unset
                $perms = unserialize( $result['permissions'] );
                unset( $result['permissions'] );
                
                // Merge and set the data
                $this->session->set('user', $result);
            }
        }
        
        // If the session time is expired
        elseif($session['expire_time'] < time() - $this->expire_time) 
        {
            $this->logout();
            return;
        }
        
        // Everything is good, user is valid, but we need to load his information
        else
        {
            // Build our query
            $query = "SELECT * FROM `pcms_accounts` 
                INNER JOIN `pcms_account_groups` ON 
                pcms_accounts.group_id = pcms_account_groups.group_id 
                WHERE id = '".$session['id']."'";
            
            // Query our database and get the users information
            $result = $this->DB->query( $query )->fetch_row();
            
            // Make sure user wasnt deleted!
            if($result == FALSE) goto Guest;
            
            // Check that our session is not being used by another user
            if($this->session->get('token') != $result['_session_id'])
            {
                $this->logout();
                goto Guest;
            }
            
            // Good to go, Update the last seen if its more then 5 minutes ago
            if((strtotime($result['last_seen']) + 300) < time())
            {
                $this->DB->query("UPDATE `pcms_accounts` SET `last_seen` = NOW() WHERE `id`=".$session['id']);
            }
            
            // Load our perms into a different var and unset
            $perms = unserialize( $result['permissions'] );
            unset( $result['permissions'] );
            
            // Custom variable for QA checking
            ($result['_account_recovery'] == NULL) ? $set = FALSE : $set = TRUE;
          
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
            $this->session->set('user', array_merge($session, $result));
        }
        
        // Load the permissions
        $this->load_permissions( $result['group_id'], $perms );
    }

/*
| ---------------------------------------------------------------
| Function: login()
| ---------------------------------------------------------------
|
| The main login script!
|
| @Param: (String) $username - The username logging in
| @Param: (String) $password - The unencrypted password
| @Return (Bool) True upon success, FALSE otherwise
|
*/

    public function login($username, $password)
    {
        // Remove white space in front and behind
        $username = trim($username);
        $password = trim($password);

        // if the username or password is empty, return FALSE
        if(empty($username) || empty($password))
        {
            output_message('error', 'login_failed_field_invalid');
            return FALSE;
        }
        
        // If the Emulator cant match the passwords, or user doesnt exist,
        // Then we spit out an error and return FALSE
        $account_id = $this->realm->validate_login($username, $password);
        if($account_id === FALSE)
        {
            output_message('error', 'login_failed_wrong_credentials');
            return FALSE;
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
            if($result === FALSE)
            {
                $r_data = $this->realm->fetch_account($account_id);
                $data = array(
                    'id' => $account_id, 
                    'username' => ucfirst(strtolower($username)), 
                    'email' => $r_data['email'], 
                    'activated' => 1,
                    'registered' => ($r_data['joindate'] == false) ? date("Y-m-d H:i:s", time()) : $r_data['joindate'],
                    'registration_ip' => $this->remote_ip
                );
                $this->DB->insert( 'pcms_accounts', $data );
                $result = $this->DB->query( $query )->fetch_row();
                
                // If the insert failed, we have a fatal error
                if($result === FALSE)
                {
                    show_error('fetal_error', FALSE, E_ERROR);
                    return FALSE;
                }
            }
            
            // Load permissions
            $perms = unserialize($result['permissions']);
            
            // Make sure we have access to our account, we have to do this after saving the session unfortunatly
            if( (!isset($perms['account_access']) || $perms['account_access'] == 0) && $result['is_super_admin'] == 0)
            {
                output_message('warning', 'account_access_denied');
                return FALSE;
            }
            
            // We are good, save permissions for this user
            $this->load_permissions($result['group_id'], $perms);
            
            // Make sure the account isnt locked due to verification
            if($result['activated'] == FALSE && config('reg_email_verification') == TRUE)
            {
                output_message('warning', 'login_failed_account_unactivated');
                return FALSE;
            }

            // Set additionals, and return true
            $time = time();
            $data['id'] = $result['id'];
            $data['logged_in'] = TRUE;
            $data['last_seen'] = $time;
            $data['expire_time'] = ($time + $this->expire_time);
            
            // We only want to save whats in the $data array to the session database.
            $this->session->set('user', $data);
            $this->session->save();
            
            // Update the sessions column to prevent more then 1 person logged in an account at a time
            $token = $this->session->get('token');
            $this->DB->update('pcms_accounts', array('_session_id' => $token), "`id`=".$data['id']);
            
            // Now add the rest of the users information
            $this->session->set('user', array_merge($result, $data));
            return TRUE;
        }
    }

/*
| ---------------------------------------------------------------
| Function: register()
| ---------------------------------------------------------------
|
| The main register script
|
| @Param: (String) $username - The username logging in
| @Param: (String) $password - The unencrypted password
| @Param: (String) $email - The email
| @Param: (Int) $sq - The secret Question ID
| @Param: (String) $sa - The secret Question answer
| @Return (Int) Account ID upon success, FALSE otherwise
|
*/

    public function register($username, $password, $email, $sq = NULL, $sa = NULL)
    {
        // Remove white space in front and behind
        $username = trim(ucfirst(strtolower($username)));
        $password = trim($password);
        $email = trim($email);

        // If the username, password, or email is empty, return FALSE
        if(empty($username) || empty($password) || empty($email))
        {
            output_message('error', 'reg_failed_field_invalid');
            return FALSE;
        }
        
        // Make sure the users IP isnt blocked
        if($this->realm->ip_banned( $this->remote_ip ) == TRUE)
        {
            output_message('error', 'reg_failed_ip_banned');
            return FALSE;
        }
        
        // If the result is not was false, then the username already exists
        if($this->realm->username_exists($username))
        {
            output_message('error', 'reg_failed_username_exists');
            return FALSE;
        }
        
        // We are good to go, register the user
        else
        {
            // Try and create the account through the emulator class
            $id = $this->realm->create_account($username, $password, $email, $this->remote_ip);
            
            // If insert into Realm Database is a success, move on
            if($id !== FALSE)
            {
                // Defaults
                $activated = 1;
                $secret = NULL;
                
                // Process account verification
                if( config('reg_email_verification') )
                {
                    $this->realm->lock_account($id);
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
                    'registration_ip' => $this->remote_ip,
                    '_account_recovery' => $secret
                );
                
                // Try and insert into pcms_accounts table
                if($this->DB->insert('pcms_accounts', $data))
                {
                    return $id;
                }
            }
            return FALSE;
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: load_permissions()
| ---------------------------------------------------------------
|
| Loads the permissions specific to this user
|
| @Return (None)
|
*/

    protected function load_permissions($gid, $perms)
    {

        // set to empty array if false
        if($perms == FALSE) $perms = array();
        
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
        $dif = FALSE;
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
| Function: load_permissions()
| ---------------------------------------------------------------
|
| Loads the permissions specific to this user
|
| @Return (Int) 1 if the user has permission, else 0
|
*/

    public function has_permission($key)
    {
        // Super admin always wins
        $user = $this->session->get('user');
        if($user['is_super_admin']) return 1;
        
        // Not a super admin, continue
        if(array_key_exists($key, $this->permissions))
        {
            return $this->permissions[$key];
        }
        return 0;
    }

/*
| ---------------------------------------------------------------
| Function: logout()
| ---------------------------------------------------------------
|
| Logs the user out and sets all session variables to Guest.
|
| @Return (None)
|
*/

    public function logout()
    {
        $this->session->destroy();
        $this->load_user();
    }
}
// EOF