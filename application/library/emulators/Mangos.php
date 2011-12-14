<?php
/* 
| --------------------------------------------------------------
| 
| Plexis
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
*/
namespace Application\Library\Emulators;

class Mangos
{

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    function __construct()
    {
        $this->load = load_class('Loader');
        $this->DB = $this->load->database( 'RDB' );
    }
    
/*
| ---------------------------------------------------------------
| Method: realmlist()
| ---------------------------------------------------------------
|
| This function gets the realmlist from the database
|
| @Return (Array) - Returns an array of realms and thier columns
|
*/
    public function realmlist()
    {
        // Grab Realms
        $query = "SELECT * FROM `realmlist`";
        return $this->DB->query( $query )->fetch_array();
    }
    
/*
| ---------------------------------------------------------------
| Method: fetch_realm()
| ---------------------------------------------------------------
|
| This function gets the realm cols. from the realmlist table
|
| @Param: (Int) $id - The realm ID we are requesting the information from
| @Return (Array) - Returns an array of cols. for the realm id
|
*/
    public function fetch_realm($id)
    {
        // Grab Realms
        $query = "SELECT * FROM `realmlist` WHERE `id`=?";
        return $this->DB->query( $query, array($id) )->fetch_row();
    }
    
/*
| ---------------------------------------------------------------
| Method: create_account()
| ---------------------------------------------------------------
|
| This function creates an account using the provided username
|   and password.
|
| @Param: (String) $username - The account username
| @Param: (String) $password - The new account (unencrypted) password
| @Param: (String) $email - The new account email
| @Param: (String) $ip - The Registeree's IP address
| @Return (Mixed) - Returns Insert ID on success, FALSE otherwise
|
*/
    public function create_account($username, $password, $email = NULL, $ip = '0.0.0.0')
    {
        // Make sure the username doesnt exist, just incase the script didnt check yet!
        if($this->username_exists($username) == TRUE)
        {
            return FALSE;
        }
        
        // SHA1 the password
        $password = $this->encrypt_password($username, $password);
        
        // Build our tables and values for Database insertion
        $data = array(
            'username' => $username, 
            'sha_pass_hash' => $password, 
            'email' => $email, 
            'last_ip' => $ip
        );
        
        // Insert into the database
        $this->DB->insert("account", $data);
        
        // If we have an affected row, then we return TRUE
        if($this->DB->num_rows() > 0)
        {
            return $this->DB->last_insert_id();
        }
        return FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Method: validate_login()
| ---------------------------------------------------------------
|
| This function takes a username and password, and logins in with
|   that information. If the password matches the pasword in the
|   database, we return the account id. Else we return FALSE,
|
| @Param: (String) $username - The account username
| @Param: (String) $password - The account (unencrypted) password
| @Return (Mixed) - Returns account ID on success, FALSE otherwise
|
*/
    public function validate_login($username, $password)
    {
        // Make sure the username doesnt exist, just incase the script didnt check yet!
        if($this->username_exists($username) == FALSE)
        {
            return FALSE;
        }
        
        // SHA1 the password
        $password = $this->encrypt_password($username, $password);
        
        // Load the users info from the Realm DB
        $query = "SELECT `id`, `sha_pass_hash` FROM `account` WHERE `username` LIKE ?";
        $result = $this->DB->query( $query, array($username) )->fetch_row();
        
        // If the result was false, then username is no good. Also match passwords.
        return ( $result['sha_pass_hash'] == $password ) ? $result['id'] : FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Method: change_password()
| ---------------------------------------------------------------
|
| This function changes the password to an account
|
| @Param: (Int) $id - The account id
| @Param: (String) $password - The new account (unencrypted) password
| @Return (Bool) - TRUE if the password is a success, FALSE otherwise
|
*/
    public function change_password($id, $password)
    {
        // Get our news posts out of the database
        $query = "SELECT `username`,`sha_pass_hash` FROM `account` WHERE `id`=?";
        $user = $this->DB->query( $query, array($id) )->fetch_row();
        
        // If didnt find the username, return FALSE
        if($user == FALSE)
        {
            return FALSE;
        }
        
        // SHA1 the password
        $password = $this->encrypt_password($user['username'], $password);
        
        // Check for a password change, If old password matches current, no need to query the DB
        if($password == $user['sha_pass_hash']) return TRUE;
        
        // Build our tables and values for Database insertion
        $data = array(
            'sha_pass_hash' => $password, 
            'sessionkey' => NULL, 
            'v' => NULL, 
            's' => NULL
        );
        
        // Update account information
        $this->DB->update("account", $data, "`id`=".$id);
        
        // If we have an affected row, then we return TRUE
        if($this->DB->num_rows() > 0)
        {
            return TRUE;
        }
        return FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Method: change_email()
| ---------------------------------------------------------------
|
| This function changes the email to an account
|
| @Param: (Int) $id - The account id
| @Param: (String) $email - The new account email
| @Return (Bool) - TRUE if the change is a success, FALSE otherwise
|
*/
    public function change_email($id, $email)
    {
        // If didnt find the account, return FALSE
        if($this->account_exists($id) == FALSE)
        {
            return FALSE;
        }
        
        // Update account information
        $this->DB->update("account", array('email' => $email), "`id`=".$id);
        
        // If we have an affected row, then we return TRUE
        if($this->DB->num_rows() > 0)
        {
            return TRUE;
        }
        return FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Method: fetch_account()
| ---------------------------------------------------------------
|
| This function queries the accounts table and pulls all the users
|   information in a formated array
|
| @Param: (Int) $id - The account ID we are loading
| @Return (Array) - returns the array of columns, FALSE otherwise
|   @Return = array(
|       'id' => Account Unique ID
|       'username' => Account Unsername
|       'email' => Account email
|       'gmlevel' => GM level, in ManGOS Format (1 -4, no A-Z shit here)
|       'joindate' => When the user joined (Date formated!)
|       'locked' => Is the account locked? (1 = yes, 0 = no)
|       'last_login' => Users last login (Date Formated!)
|       'last_ip' => Users last seen IP
|       'Expansion' => Expansion ID
|   );
|
*/
    public function fetch_account($id)
    {
        // Check the Realm DB for this username
        $query = "SELECT * FROM `account` WHERE `id`= ?";
        $temp = $this->DB->query( $query, array($id) )->fetch_row();
        
        // If the result is NOT false, we have a match, username is taken
        if($temp !== FALSE)
        {
            return array(
                'id' => $temp['id'],
                'username' => $temp['username'],
                'email' => $temp['email'],
                //'gmlevel' => $temp['gmlevel'],
                'joindate' => $temp['joindate'],
                'locked' => $temp['locked'],
                'last_login' => $temp['last_login'],
                'last_ip' => $temp['last_ip'],
                'expansion' => $temp['expansion']
            );
        }
        return FALSE;
    }
    
    
/*
| ---------------------------------------------------------------
| Method: username_exists()
| ---------------------------------------------------------------
|
| This function queries the accounts table and finds if the given
|   username is already taken.
|
| @Param: (String) $username - The username we are checking for
| @Return (Bool) - TRUE if the username is used already, FALSE otherwise
|
*/
    public function username_exists($username)
    {
        // Check the Realm DB for this username
        $query = "SELECT `id` FROM `account` WHERE `username` LIKE ?";
        $res = $this->DB->query( $query, array($username) )->fetch_column();
        
        // If the result is NOT false, we have a match, username is taken
        if($res !== FALSE)
        {
            return TRUE;
        }
        return FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Method: account_exists()
| ---------------------------------------------------------------
|
| This function queries the accounts table and finds if the given
|   account ID exists.
|
| @Param: (Int) $id - The account ID we are checking for
| @Return (Bool) - TRUE if the id exists, FALSE otherwise
|
*/
    public function account_exists($id)
    {
        // Check the Realm DB for this username
        $query = "SELECT `username` FROM `account` WHERE `id` LIKE ?";
        $res = $this->DB->query( $query, array($id) )->fetch_column();
        
        // If the result is NOT false, we have a match, username is taken
        if($res !== FALSE)
        {
            return TRUE;
        }
        return FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Method: email_exists()
| ---------------------------------------------------------------
|
| This function queries the accounts table and finds if the given
|   email exists.
|
| @Param: (String) $email - The email we are checking for
| @Return (Bool) - TRUE if the id exists, FALSE otherwise
|
*/
    public function email_exists($email)
    {
        // Check the Realm DB for this username
        $query = "SELECT `username` FROM `account` WHERE `email` LIKE ?";
        $res = $this->DB->query( $query, array($email) )->fetch_column();
        
        // If the result is NOT false, we have a match, username is taken
        if($res !== FALSE)
        {
            return TRUE;
        }
        return FALSE;
    }

/*
| ---------------------------------------------------------------
| Function: account_banned()
| ---------------------------------------------------------------
|
| Checks the realm database if the account is banned
|
| @Param: (Int) $account_id - The account id we are checking
| @Return (Bool) Returns TRUE if the account is banned
|
*/
    public function account_banned($account_id)
    {
        $query = "SELECT COUNT(*) FROM `account_banned` WHERE `active`=1 AND `id`=?";
        $check = $this->DB->query( $query, array($account_id) )->fetch_column();
        if ($check !== FALSE && $check > 0)
        {
            return TRUE; // Account is banned
        }
        else
        {
            return FALSE; // Account is not banned
        }
    }

/*
| ---------------------------------------------------------------
| Function: ip_banned()
| ---------------------------------------------------------------
|
| Checks the realm database if the users IP is banned
|
| @Param: (String) $ip - The IP we are checking
| @Return (Bool) Returns TRUE if the account is banned
|
*/
    public function ip_banned($ip)
    {
        $query = "SELECT COUNT(*) FROM `ip_banned` WHERE `ip`=?";
        $check = $this->DB->query( $query, array($ip) )->fetch_column();
        if ($check !== FALSE && $check > 0)
        {
            return TRUE; // Ip is banned
        }
        else
        {
            return FALSE; // Ip is not banned
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: account_locked()
| ---------------------------------------------------------------
|
| Checks the realm database if the account is locked
|
| @Param: (Int) $account_id - The account id we are checking
| @Return (Bool) Returns TRUE if the account is banned
|
*/
    public function account_locked($account_id)
    {
        $query = "SELECT `locked` FROM `account` WHERE `id`=?";
        $check = $this->DB->query( $query, array($account_id) )->fetch_column();
        if($check !== FALSE && $check == 1)
        {
            return TRUE; // Account is locked
        }
        else
        {
            return FALSE; // Account is not locked
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: ban_account()
| ---------------------------------------------------------------
|
| Bans a user account
|
| @Param: (Int) $id - The account ID
| @Param: (String) $banreason - The reason user is being banned
| @Param: (String) $unbandate - The unban date timestamp
| @Param: (String) $banedby - Who is banning the user?
| @Param: (Bool) $banip - Ban ip as well?
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function ban_account($id, $banreason, $unbandate = NULL, $bannedby = 'Admin', $banip = FALSE)
    {
        
        // Check for account existance
        if(!$this->account_exists($id))
        {
            return FALSE;
        }

        // Make sure our unbandate is set, 1 year default
        ($unbandate == NULL) ? $unbandate = (time() + 31556926) : '';
		$data = array(
			'id' => $id,
			'bandate' => time(), 
			'unbandate' => $unbandate, 
			'bannedby' => $bannedby, 
			'banreason' => $banreason, 
			'active' => 1
        ); 
        $result = $this->DB->insert('account_banned', $data);
        
        // Do we ban the IP as well?
        if($banip == TRUE && $result == TRUE)
        {
            return $this->ban_account_ip($id, $banreason, $unbandate, $bannedby);
        }
        return $result;
    }
    
/*
| ---------------------------------------------------------------
| Method: ban_account_ip()
| ---------------------------------------------------------------
|
| Bans an accounts IP address
|
| @Param: (Int) $id - The account ID
| @Param: (String) $banreason - The reason user is being banned
| @Param: (String) $unbandate - The unban date timestamp
| @Param: (String) $banedby - Who is banning the user?
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function ban_account_ip($id, $banreason, $unbandate = NULL, $bannedby = 'Admin')
    {
        // Check for account existance
        $query = "SELECT `last_ip` FROM `account` WHERE `id`=?";
        $ip = $this->DB->query( $query, array($id) )->fetch_column();
        if(!$ip)
        {
            return FALSE;
        }
        
        // Check if the IP is already banned or not
        if( $this->ip_banned($ip) ) return TRUE;

        // Make sure our unbandate is set, 1 year default
        ($unbandate == NULL) ? $unbandate = (time() + 31556926) : '';
		$data = array(
			'ip' => $ip,
			'bandate' => time(), 
			'unbandate' => $unbandate, 
			'bannedby' => $bannedby, 
			'banreason' => $banreason, 
        ); 
        return $this->DB->insert('ip_banned', $data);
    }
    
/*
| ---------------------------------------------------------------
| Method: unban_account()
| ---------------------------------------------------------------
|
| Un-Bans a user account
|
| @Param: (Int) $id - The account ID
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function unban_account($id)
    {
        // Check if the account is not Banned
        if( !$this->account_banned($id) ) return TRUE;
        
        // Check for account existance
        return $this->DB->update("account_banned", array('active' => 0), "`id`=".$id);
    }
    
/*
| ---------------------------------------------------------------
| Method: unban_account_ip()
| ---------------------------------------------------------------
|
| Un-Bans a users account IP
|
| @Param: (Int) $id - The account ID
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function unban_account_ip($id)
    {
        // Check for account existance
        $query = "SELECT `last_ip` FROM `accounts` WHERE `id`=?";
        $ip = $this->DB->query( $query, array($id) )->fetch_column();
        if(!$ip)
        {
            return FALSE;
        }
        
        // Check if the IP is banned or not
        if( !$this->ip_banned($ip) ) return TRUE;
        
        // Check for account existance
        return $this->DB->delete("ip_banned", "`ip`=".$ip);
    }
    
/*
| ---------------------------------------------------------------
| Method: delete_account()
| ---------------------------------------------------------------
|
| Un-Bans a users account IP
|
| @Param: (Int) $id - The account ID
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function delete_account($id)
    {
        // Delete any bans
        $this->unban_account($id);
        
        // Delete the account
        return $this->DB->delete("account", "`id`=".$id);
    }
    
/*
| ---------------------------------------------------------------
| Method: lock_account()
| ---------------------------------------------------------------
|
| Locks a user account
|
| @Param: (Int) $id - The account ID
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function lock_account($id)
    {
        // Check if the account is not Banned
        if( $this->account_locked($id) ) return TRUE;
        
        // Check for account existance
        return $this->DB->update("account", array('locked' => 1), "`id`=".$id);
    }
    
/*
| ---------------------------------------------------------------
| Method: unlock_account()
| ---------------------------------------------------------------
|
| UnLocks a user account
|
| @Param: (Int) $id - The account ID
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function unlock_account($id)
    {
        // Check if the account is not Banned
        if( !$this->account_locked($id) ) return TRUE;
        
        // Check for account existance
        return $this->DB->update("account", array('locked' => 0), "`id`=".$id);
    }
   
/*
| ---------------------------------------------------------------
| Function: encrypt_password()
| ---------------------------------------------------------------
|
|  Converts the Username / Password into a server specific encryption
|
| @Param: (String) $login - The username
| @Param: (String) $pass - The password
| @Return (Mixed) Returns the SHA1
|
*/
    public function encrypt_password($login, $pass)
    {
        $user = strtoupper($login);
        $pass = strtoupper($pass);
        return SHA1($user.':'.$pass);
    }
}
// EOF