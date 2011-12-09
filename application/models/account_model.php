<?php
class Account_Model extends System\Core\Model 
{

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    function __construct()
    {
        parent::__construct();
        $this->DB = $this->load->database( 'DB' );
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
        Gen:
        {
            // Create a really random string and length it to 15 characters
            $str = microtime(1);
            $key = sha1(base64_encode(pack("H*", md5(utf8_encode($str)))));
            $key = substr($key, 0, 15);
        }
        
        // make sure this key doesnt already exist
        $result = $this->DB->query("SELECT `id` FROM `pcms_account_keys` WHERE `key`=?", array($key))->fetch_column();
        if($result !== FALSE) goto Gen;
        
        // Insert the key into the DB
        $result = $this->DB->insert('pcms_account_keys', array('key' => $key, 'username' => $username));
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
        $result = $this->DB->query("SELECT `username` FROM `pcms_account_keys` WHERE `key`=?", array($key))->fetch_column();
        if($result !== FALSE)
        {
            // Return the username
            return $result;
        }
        // If we are here, there the key didnt exist
        return FALSE; 
    }
}
// EOF