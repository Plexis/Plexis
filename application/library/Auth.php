<?php
/* 
| --------------------------------------------------------------
| 
| Plexis
|
| --------------------------------------------------------------
|
| Author: 		Steven Wilson
| Copyright:	Copyright (c) 2011, Steven Wilson
| License: 		GNU GPL v3
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
                
                // Merge and set the data
                $this->session->set('user', $result);
            }
        }
        
        // If the session time is expired
        elseif($session['expire_time'] < time() - $this->expire_time) 
        {
            $this->logout();
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
            
            // Custom variable for QA checking
            $set = TRUE;
            if($result['_account_recovery'] == NULL)
            {
                $set = FALSE;
            }
            
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
                    'username' => $username, 
                    'email' => $r_data['email'], 
                    'activated' => 1
                );
                $this->DB->insert( 'pcms_accounts', $data );
                $result = $this->DB->query( $query )->fetch_row();
                
                // If the insert failed, we have a fatal error
                if($result === FALSE)
                {
                    show_error('fetal_error', FALSE, E_ERROR);
                }
            }
            
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
| @Return (Bool) True upon success, FALSE otherwise
|
*/

    public function register($username, $password, $email, $sq = NULL, $sa = NULL)
    {
        // Remove white space in front and behind
        $username = trim($username);
        $password = trim($password);
        $email = trim($email);
        $secret = NULL;

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
                // Default
                $activated = 1;
                
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
                        'answer' => $sa,
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
                    return TRUE;
                }
            }
            return FALSE;
        }
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