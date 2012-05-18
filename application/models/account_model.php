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
| Class: Account_Model()
| ---------------------------------------------------------------
|
| Model for the Account controller
|
*/
class Account_Model extends Application\Core\Model 
{

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    public function __construct()
    {
        parent::__construct();
    }

/*
| ---------------------------------------------------------------
| Function: create_key
| ---------------------------------------------------------------
|
| Creates a verification key for an account
|
*/    
    public function create_key($username)
    {
        // Create a really random string and length it to 20 characters
        $str = microtime(1);
        $key = sha1(base64_encode(pack("H*", md5(utf8_encode($str)))));
        $key = substr($key, 0, 20);

        // Insert the key into the DB
        $result = $this->DB->update('pcms_accounts', array('_activation_code' => $key), "`username`='".$username."'");
        if($result == TRUE) return $key;
        
        // If we are here, there was a DB error
        return FALSE; 
    }
    
/*
| ---------------------------------------------------------------
| Function: verify_key
| ---------------------------------------------------------------
|
| Verifies a verification key for an account
|
*/    
    public function verify_key($key)
    {
        // make sure this key doesnt already exist
        $result = $this->DB->query("SELECT `username` FROM `pcms_accounts` WHERE `_activation_code`=?", array($key))->fetch_column();
        if($result !== FALSE)
        {
            // Return the username
            return $result;
        }
        // If we are here, there the key didnt exist
        return FALSE; 
    }
    
/*
| ---------------------------------------------------------------
| Function: get_recovery_data
| ---------------------------------------------------------------
|
| The process decodes the account recovery data, and returns it
|
*/     
    public function get_recovery_data($username)
    {
        // Build the query, and grab the data
        $query = 'SELECT `id`, `email`, `_account_recovery` FROM `pcms_accounts` WHERE `username`=?';
        $info = $this->DB->query( $query, array($username))->fetch_row();
        
        // See if the recovery data is NULL
        if($info === FALSE || $info['_account_recovery'] == NULL)
        {
            return ($info === FALSE) ? FALSE : NULL;
        }
        
        // Unserialize and decode out recoery data
        $data = unserialize( base64_decode($info['_account_recovery']) );
        $questions = get_secret_questions('array', TRUE);
        return array(
            'id' => $info['id'],
            'email' => $info['email'],
            'question' => $questions[ $data['id'] ], 
            'answer' => $data['answer'],
            'registration_email' => $data['email']
        );
    }
    
/*
| ---------------------------------------------------------------
| Function: set_recovery_data
| ---------------------------------------------------------------
|
| The process sets the account recovery info for an account
|
*/     
    public function set_recovery_data($username, $qid, $answer)
    {
        // Build the query, and grab the data
        $old = $this->get_recovery_data($username);
        
        // Make sure user exists!
        if($old === FALSE) return FALSE;
        
        // If we have no previous data, then we need to create new
        if($old == NULL)
        {
            $query = 'SELECT `id`, `email` FROM `pcms_accounts` WHERE `username`=?';
            $old = $this->DB->query( $query, array($username))->fetch_row();
        }
        
        // Build our recovery data string
        $array = array(
            'id' => $qid,
            'answer' => $answer,
            'email' => $old['email']
        );
        $secret = base64_encode( serialize($array) );
        
        // Update the DB
        return $this->DB->update('pcms_accounts', array('_account_recovery' => $secret), "`username`='".$username."'");
    }

/*
| ---------------------------------------------------------------
| Function: process_recovery
| ---------------------------------------------------------------
|
| The main process of recovering an account by changing the password
|
*/     
    public function process_recovery($id, $username, $email)
    {
        // Generate a new password, 8 characters long
        $key = sha1($username . microtime(1));
        $password = substr($key, 0, 8);
        
        // Tell the realm to change the pass
        $result = $this->realm->change_password($id, $password);
        
        // Check the result, if FALSE then we failed to change the password :O
        if($result === FALSE)
        {
            output_message('error', 'account_recover_pass_failed');
            return FALSE;
        }
        
        // Send a success message and include the new password there
        output_message('success', 'account_recover_pass_success', array($password));
        return TRUE;
    }
    
/*
| ---------------------------------------------------------------
| Function: create_invite_key()
| ---------------------------------------------------------------
|
| This method creates an invite key for the user
|
| @Param: (Int) $userid - The users id we are creating a key from
| @Return (Mixed) Returns new key, or false on failure
|
*/
    public function create_invite_key($userid)
    {
        // Load the input class
        $this->input = load_class('Input');
        
        // Get a string containing the current IP address represented as a long integer
        // and the current Unix timestamp with microsecond prescision.
        $longid = sprintf("%u%d", ip2long($this->input->ip_address()), microtime(true));
        
        //Each invitation key consists of a SHA1 hash of the above 'longid' prepended with the user's name.
        $new_key = substr(sha1($userid . $longid), 0, 30);
        
        $key_query_data = array(
            "key" => $new_key, 
            "sponser" => $userid
        );
        
        // Insert it into the pcms_reg_keys table.
        $result = $this->DB->insert("pcms_reg_keys", $key_query_data);
        return ($result !== false) ? $new_key : false;
    }
    
/*
| ---------------------------------------------------------------
| Function: create_invite_key()
| ---------------------------------------------------------------
|
| This method deletes an invite key for the user
|
| @Param: (Int) $userid - The users id we are deleteing a key from
| @Param: (Int) $keyid - The id of the key
| @Return (Bool)
|
*/
    public function delete_invite_key($userid, $keyid)
    {
        // Build our query, fetch the key
        $query = "SELECT * FROM `pcms_reg_keys` WHERE `id` = ?";
        $result = $this->DB->query($query, array($keyid))->fetch_row();
        
        // Check to make sure the query didn't fail.
        if( $result !== false )
        {
            $sponsor = $result["sponser"];
            
            // Only allow the key to be deleted if it belongs to the currently logged in user.
            $result = ( $sponsor == $userid ) ? $this->DB->delete("pcms_reg_keys", "`id` = '$keyid'") : false;
        }
        return ($result !== false);
    }

    
/*
| ---------------------------------------------------------------
| Function: get_id()
| ---------------------------------------------------------------
|
|  Retrives the users id number using his account name
|
| @Param: (String) $name - The username you are getting the ID for
| @Return (Int) Returns the account ID
|
*/
    public function get_id($name)
    {
        $this->DB->query("SELECT `id` FROM `pcms_accounts` WHERE `username` LIKE :name LIMIT 1", array(':name' => $name));
        return $this->DB->fetch_column();
    }

/*
| ---------------------------------------------------------------
| Function: get_profile()
| ---------------------------------------------------------------
|
| Returns all the users information such as email, reg date etc etc
|
| @Param: (Int) $id - The account ID you are getting the info for
| @Return (Array) An array of each column in the users table
|
*/
    public function get_profile($id)
    {
        $this->DB->query("SELECT * FROM `pcms_accounts` WHERE `id`=?", array($id));
        return $this->DB->fetch_row();
    }
}
// EOF