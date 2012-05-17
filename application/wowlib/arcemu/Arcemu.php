<?php
/* 
| --------------------------------------------------------------
| 
| Plexis
|
| --------------------------------------------------------------
|
| Author:       Tony Hudgins
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
*/
namespace Wowlib;

class Arcemu
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
        //$query = "SELECT * FROM `realmlist`";
        //return $this->DB->query( $query )->fetch_array();
        
        //This function doesn't return anything since ArcEmu doesn't store the realms in a data table.
        return array();
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
        //$query = "SELECT * FROM `realmlist` WHERE `id`=?";
        //return $this->DB->query( $query, array($id) )->fetch_row();
        
        //Again, doesn't return anything.
        return array();
    }
    
/*
| ---------------------------------------------------------------
| Method: uptime()
| ---------------------------------------------------------------
|
| This function gets the realms $id uptime
|
| @Param: (Int) $id - The realm ID we are requesting the information from
| @Return (Int) Time string of FALSE if unavailable
|
*/
    public function uptime($id)
    {
        return FALSE;
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
| @Return: Returns the new Account ID on success, FALSE otherwise
|
*/
    public function create_account($username, $password, $email = NULL, $ip = '0.0.0.0')
    {
        // Make sure the username doesnt exist, just incase the script didnt check yet!
        if($this->username_exists($username) == TRUE)
        {
            return FALSE;
        }
        
        // Build our tables and values for Database insertion
        $data = array(
            'login' => $username, 
            'password' => $password, 
            'encrypted_password' => $this->encrypt_password($username, $password),
            'email' => $email, 
            'lastip' => $ip
        );
        
        // Insert into the database
        $this->DB->insert("accounts", $data);
        
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
        
        // Load the users info from the Realm DB
        $query = "SELECT `acct`, `password` FROM `accounts` WHERE `login`=?";
        $result = $this->DB->query( $query, array($username) )->fetch_row();
        
        // If the result was false, then username is no good. Also match passwords.
        return ( $result['password'] == $password ) ? $result['acct'] : FALSE;
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
        $query = "SELECT `login`,`password` FROM `accounts` WHERE `acct`=?";
        $user = $this->DB->query( $query, array($id) )->fetch_row();
        
        // If didnt find the username, return FALSE
        if($user == FALSE)
        {
            return FALSE;
        }
        
        // Check for a password change, If old password matches current, no need to query the DB
        if($password == $user['password']) return TRUE;
        
        // Build our tables and values for Database insertion
        $data = array(
            'password' => $password, 
            'encrypted_password' => $this->encrypt_password($user['login'], $password)
        );
        
        // Update account information
        $this->DB->update("accounts", $data, "`acct`=".$id);
        
        // If we have an affected row, then we return TRUE
        return ($this->DB->num_rows() > 0);
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
        $this->DB->update("accounts", array('email' => $email), "`acct`=".$id);
        
        // If we have an affected row, then we return TRUE
        return ($this->DB->num_rows() > 0);
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
|       'joindate' => When the user joined***
|       'locked' => Is the account locked? (1 = yes, 0 = no)
|       'banned' => Is the account banned? (1 = yes, 0 = no)
|       'last_login' => Users last login***
|       'last_ip' => Users last seen IP
|       'Expansion' => Expansion ID
|   );
|
|   *** Date formated as so! (mysql timestamp) = date("Y-m-d H:i:s", $timestamp)
|
*/
    public function fetch_account($id)
    {
        // Check the Realm DB for this username
        $query = "SELECT * FROM `accounts` WHERE `acct`= ?";
        $temp = $this->DB->query( $query, array($id) )->fetch_row();
        
        // If the result is NOT false, we have a match, username is taken
        if($temp !== FALSE)
        {
            return array(
                'id' => $temp['acct'],
                'username' => $temp['login'],
                'email' => $temp['email'],
                //'gmlevel' => $temp['gmlevel'],
                'joindate' => false, //ArcEmu doesn't store a join date, return false
                'locked' => $temp['banned'],
                'banned' => (int) $this->account_banned($temp['id']),
                'last_login' => $temp['lastlogin'],
                'last_ip' => $temp['lastip'],
                'expansion' => $temp['flags']
            );
        }
        return FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Method: get_account_name()
| ---------------------------------------------------------------
|
| This function queries the accounts table and gets the username
| to the specific account ID
|
| @Param: (Int) $id - The id for the account
| @Return (String) - Returns the account name, FALSE otherwise
|
*/
    public function get_account_name($id)
    {
        // Build our query
        $query = "SELECT `username` FROM `accounts` WHERE `acct`= ?";
        $temp = $this->DB->query( $query, array($id) )->fetch_column();
        
        return ($temp == false) ? false : $temp;
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
        $query = "SELECT `acct` FROM `accounts` WHERE `login`=?";
        $res = $this->DB->query( $query, array($username) )->fetch_column();
        
        // If the result is NOT false, we have a match, username is taken
        return ($res !== FALSE);
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
        $query = "SELECT `login` FROM `accounts` WHERE `acct`=?";
        $res = $this->DB->query( $query, array($id) )->fetch_column();
        
        // If the result is NOT false, we have a match, username is taken
        return ($res !== FALSE);
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
        $query = "SELECT `login` FROM `account` WHERE `email`=?";
        $res = $this->DB->query( $query, array($email) )->fetch_column();
        
        // If the result is NOT false, we have a match, username is taken
        return ($res !== FALSE);
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
        $query = "SELECT COUNT(*) FROM `accounts` WHERE `banned` > 0 AND `acct` = ?;";
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
        $query = "SELECT COUNT(*) FROM `ipbans` WHERE `ip`=?";
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
        $query = "SELECT `banned` FROM `accounts` WHERE `acct`=?";
        $check = $this->DB->query( $query, array($account_id) )->fetch_column();
        if($check !== FALSE && $check > 1)
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
            'banned' => $unbandate, 
            'banreason' => $banreason
        ); 
        $result = $this->DB->update('accounts', $data, "`acct` = '$id'");
        
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
        $query = "SELECT `lastip` FROM `accounts` WHERE `acct`=?";
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
            'expire' => $unbandate,
            'banreason' => $banreason, 
        ); 
        return $this->DB->insert('ipbans', $data);
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
        return $this->DB->update("accounts", array('banned' => 0, 'banreason' => ''), "`acct`=".$id);
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
        $query = "SELECT `lastip` FROM `accounts` WHERE `acct`=?";
        $ip = $this->DB->query( $query, array($id) )->fetch_column();
        if(!$ip)
        {
            return FALSE;
        }
        
        // Check if the IP is banned or not
        if( !$this->ip_banned($ip) ) return TRUE;
        
        // Check for account existance
        return $this->DB->delete("ipbans", "`ip`=".$ip);
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
        return $this->DB->delete("accounts", "`acct`=".$id);
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
        return $this->DB->update("accounts", array('banned' => time() + 31556926, "banreason" => "Psuedo account lock by Plexis CMS."), "`acct`=".$id);
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
        return $this->DB->update("accounts", array('banned' => 0, "banreason" => ''), "`acct`=".$id);
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
    
/*
| ---------------------------------------------------------------
| Function: get_expansion_info()
| ---------------------------------------------------------------
|
|  Gets information about account expansions.
|
| @Return (Array) Returns an array containing the expansions and all relevant information.
|
*/
    
    public function get_expansion_info()
    {
        return array
            (
                //Expansion ID => Expansion Name
                0  => "Classic",
                8  => "The Burning Crusade",
                16 => "Wrath of the Lich King Only", //Accounts that only have WotLK activated, not WotLK and TBC (largely unused, but ArcEmu does support it...)
                24 => "Wrath of the Lich King and The Burning Crusade",
                32 => "Cataclysm"
            );
    }
    
/*
| ---------------------------------------------------------------
| Function: get_expansion()
| ---------------------------------------------------------------
|
|  Returns the name of the expansion from the given ID.
|
| @Param: (Int) $id - The account ID.
| @Param: (Bool) $string - Whether or not to return the expansion ID as it in the accounts table, or the name of the expansion.
| @Return (Mixed) Returns the current expansion (ID number or name) on success, FALSE on failure.
|
*/
    
    public function get_expansion($id, $string = FALSE)
    {
        // Fetch account, if it doesnt exists, return FALSE
        $account = $this->fetch_account($id);
        if( !$account ) return FALSE;
        
        // Do we return as a string, or expansion ID?
        if( !$string )
        {
            return $account['expansion'];
        }
        else
        {
            // Get the expansion name string, and return it if it exists
            $expansion_data = $this->get_expansion_info();
            if( array_key_exists($expansion_data, $id) )
            {
                return $expansion_data[$id];
            }
            else
            {
                return FALSE;
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: update_expansion()
| ---------------------------------------------------------------
|
|  Sets the expansion on the specified account.
|
| @Param: (Int) $id - The expansion ID.
| @Param: (Int) $account - The account ID.
| @Return (Bool) FALSE on failure, TRUE on success.
|
*/
    
    public function update_expansion($id, $account)
    {
        // If the account doesnt exist, return FALSE
        if( !$this->account_exists($account) ) return FALSE;

        // Update the expansion
        return $this->DB->update("accounts", array('flags' => $id), "`acct` = '$account'");
    }
    
/*
| ---------------------------------------------------------------
| Function: get_account_count()
| ---------------------------------------------------------------
|
|  This methods returns the number of accounts in the accounts table.
|
| @Return (Int) The number of accounts
|
*/
    
    public function get_account_count()
    {
        return $this->DB->query("SELECT count(id) FROM `account`")->fetch_column();
    }
    
/*
| ---------------------------------------------------------------
| Function: get_banned_count()
| ---------------------------------------------------------------
|
|  This methods returns the number of accounts in the accounts table.
|
| @Return (Int) The number of accounts
|
*/
    
    public function get_banned_count()
    {
        return $this->DB->query("SELECT count(id) FROM `account_banned` WHERE `active` = 1")->fetch_column();
    }
    
/*
| ---------------------------------------------------------------
| Function: get_inactive_account_count()
| ---------------------------------------------------------------
|
|  This methods returns the number of accounts that havent logged
|   in withing the last 3 months
|
| @Return (Int) The number of accounts
|
*/
    
    public function get_inactive_account_count()
    {
        // 90 days or older
        $time = time() - 7776000;
        $query = "SELECT COUNT(*) FROM `pcms_accounts` WHERE UNIX_TIMESTAMP(`last_seen`) <  $time";
        return $this->DB->query("SELECT count(id) FROM `account`")->fetch_column();
    }
    
/*
| ---------------------------------------------------------------
| Function: get_active_account_count()
| ---------------------------------------------------------------
|
|  This methods returns the number of accounts that have logged
|   in withing the last 24 hours
|
| @Return (Int) The number of accounts
|
*/
    
    public function get_active_account_count()
    {
        // 90 days or older
        $time = date("Y-m-d H:i:s", time() - 86400);
        $query = "SELECT COUNT(*) FROM `account` WHERE `last_login` BETWEEN  '$time' AND NOW()";
        return $this->DB->query( $query )->fetch_column();
    }
}
// EOF