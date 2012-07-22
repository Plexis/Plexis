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
namespace Wowlib;

class Mangos
{

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    function __construct()
    {
        $this->load = load_class('Loader');
        $this->DB = $this->load->database('RDB');
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
        $query = "SELECT * FROM `realmlist` ORDER BY `id`";
        return $this->DB->query( $query )->fetch_array();
    }
    
/*
| ---------------------------------------------------------------
| Method: fetchRealm()
| ---------------------------------------------------------------
|
| This function gets the realm cols. from the realmlist table
|
| @Param: (Int) $id - The realm ID we are requesting the information from
| @Return (Array) - Returns an array of cols. for the realm id
|
*/
    public function fetchRealm($id)
    {
        // Grab Realms
        $query = "SELECT * FROM `realmlist` WHERE `id`=?";
        return $this->DB->query( $query, array($id) )->fetch_row();
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
        // Grab Realms
        $query = "SELECT MAX(`starttime`) FROM `uptime` WHERE `realmid`=?";
        $result = $this->DB->query( $query, array($id) )->fetch_column();
        return (time() - $result);
    }
    
/*
| ---------------------------------------------------------------
| Method: createAccount()
| ---------------------------------------------------------------
|
| This function creates an account using the provided username
|   and password.
|
| @Param: (String) $username - The account username
| @Param: (String) $password - The new account (unencrypted) password
| @Param: (String) $email - The new account email
| @Param: (String) $ip - The Registeree's IP address
| @Return (Mixed) - Returns the new Account ID on success, FALSE otherwise
|
*/
    public function createAccount($username, $password, $email = NULL, $ip = '0.0.0.0')
    {
        // Make sure the username doesnt exist, just incase the script didnt check yet!
        if($this->accountExists($username)) return false;
        
        // SHA1 the password
        $user = strtoupper($username);
        $pass = strtoupper($password);
        $password = sha1($user.':'.$pass);
        
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
        return ($this->DB->num_rows() > 0) ? $this->DB->last_insert_id() : false;
    }
    
/*
| ---------------------------------------------------------------
| Method: validate()
| ---------------------------------------------------------------
|
| This method takes a username and password, and logins in with
|   that information. If the password matches the pasword in the
|   database, we return the account id. Else we return FALSE,
|
| @Param: (String) $username - The account username
| @Param: (String) $password - The account (unencrypted) password
| @Return (Mixed) - Returns account ID on success, FALSE otherwise
|
*/
    public function validate($username, $password)
    {
        // Make sure the username doesnt exist, just incase the script didnt check yet!
        if(!$this->accountExists($username)) return false;
        
        // SHA1 the password
        $user = strtoupper($username);
        $pass = strtoupper($password);
        $password = sha1($user.':'.$pass);
        
        // Load the users info from the Realm DB
        $query = "SELECT `id`, `sha_pass_hash` FROM `account` WHERE `username`=?";
        $result = $this->DB->query( $query, array($username) )->fetch_row();
        
        // If the result was false, then username is no good. Also match passwords.
        return ( $result['sha_pass_hash'] == $password ) ? $result['id'] : false;
    }
    
/*
| ---------------------------------------------------------------
| Method: fetchAccount()
| ---------------------------------------------------------------
|
| This function queries the accounts table and pulls all the users
|   information into an object
|
| @Param: (Int) $id - The account ID we are loading
| @Return (Object) - returns the account object
|
*/
    public function fetchAccount($id)
    {
        try {
            $account = new Account($id, $this);
        }
        catch(\Exception $e) {
            $account = false;
        }
        return $account;
    }
    
/*
| ---------------------------------------------------------------
| Method: accountExists()
| ---------------------------------------------------------------
|
| This function queries the accounts table and finds if the given
|   account ID exists.
|
| @Param: (Int | String) $id - The account ID we are checking for,
|   or the account username
| @Return (Bool) - TRUE if the id exists, FALSE otherwise
|
*/
    public function accountExists($id)
    {
        // Check the Realm DB for this username / account ID
        if(is_numeric($id))
            $query = "SELECT `username` FROM `account` WHERE `id`=?";
        else
            $query = "SELECT `id` FROM `account` WHERE `username` LIKE ? LIMIT 1";

        // If the result is NOT false, we have a match, username is taken
        $res = $this->DB->query( $query, array($id) )->fetch_column();
        return ($res !== false);
    }
    
/*
| ---------------------------------------------------------------
| Method: emailExists()
| ---------------------------------------------------------------
|
| This function queries the accounts table and finds if the given
|   email exists.
|
| @Param: (String) $email - The email we are checking for
| @Return (Bool) - TRUE if the id exists, FALSE otherwise
|
*/
    public function emailExists($email)
    {
        // Check the Realm DB for this username
        $query = "SELECT `username` FROM `account` WHERE `email`=?";
        $res = $this->DB->query( $query, array($email) )->fetch_column();
        
        // If the result is NOT false, we have a match, username is taken
        return ($res !== false);
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
    public function accountBanned($account_id)
    {
        $query = "SELECT COUNT(*) FROM `account_banned` WHERE `active`=1 AND `id`=?";
        $check = $this->DB->query( $query, array($account_id) )->fetch_column();
        return ($check !== FALSE && $check > 0) ? true : false;
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
    public function ipBanned($ip)
    {
        $query = "SELECT COUNT(*) FROM `ip_banned` WHERE `ip`=?";
        $check = $this->DB->query( $query, array($ip) )->fetch_column();
        return ($check !== FALSE && $check > 0) ? true : false;
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
    public function banAccount($id, $banreason, $unbandate = NULL, $bannedby = 'Admin', $banip = false)
    {
        
        // Check for account existance
        if(!$this->accountExists($id)) return false;

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
        if($banip == true && $result == true)
        {
            return $this->banAccountIp($id, $banreason, $unbandate, $bannedby);
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
    public function banAccountIp($id, $banreason, $unbandate = NULL, $bannedby = 'Admin')
    {
        // Check for account existance
        $query = "SELECT `last_ip` FROM `account` WHERE `id`=?";
        $ip = $this->DB->query( $query, array($id) )->fetch_column();
        if(!$ip) return false;
        
        // Check if the IP is already banned or not
        if( $this->ipBanned($ip) ) return true;

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
| Method: unbanAccount()
| ---------------------------------------------------------------
|
| Un-Bans a user account
|
| @Param: (Int) $id - The account ID
| @Return (Bool) TRUE on success, FALSE on failure
|
*/ 
    public function unbanAccount($id)
    {
        // Check if the account is not Banned
        if( !$this->accountBanned($id) ) return true;
        
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
    public function unbanAccountIp($id)
    {
        // Check for account existance
        $query = "SELECT `last_ip` FROM `accounts` WHERE `id`=?";
        $ip = $this->DB->query( $query, array($id) )->fetch_column();
        if(!$ip) return false;
        
        // Check if the IP is banned or not
        if( !$this->ipBanned($ip) ) return true;
        
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
    public function deleteAccount($id)
    {
        // Delete any bans
        $this->unbanAccount($id);
        
        // Delete the account
        return $this->DB->delete("account", "`id`=".$id);
    }
    
/*
| ---------------------------------------------------------------
| Function: expansions()
| ---------------------------------------------------------------
|
| Returns an array of supported expansions by this realm. Donot
| include expansions that arent supported in this array!
|
| @Return (Array)
|   0 => None, Base Game
|   1 => Burning Crusade
|   2 => WotLK
|   3 => Cata (If Supported)
|   4 => MoP (If Supported)
|
*/
    
    public function expansions()
    {
        // Expansion ID => Expansion Name
        return array(
            0 => "Classic",
            1 => "The Burning Crusade",
            2 => "Wrath of the Lich King"
        );
    }
    
/*
| ---------------------------------------------------------------
| Function: expansionToText()
| ---------------------------------------------------------------
|
| Returns the expansion text name
|
| @Return (String) Returns false if the expansion doesnt exist
|
*/
    
    public function expansionToText($id = 0)
    {
        // return all expansions if no id is passed
        $exp = $this->expansions();
        return (isset($exp[$id])) ? $exp[$id] : false;
    }
    
/*
| ---------------------------------------------------------------
| Function: expansionToBit()
| ---------------------------------------------------------------
|
| Returns the Database ID of the given expansion
|
| @Return (Int)
|
*/
    
    public function expansionToBit($e)
    {
        switch($e)
        {
            case 0: // Base Game
                return 0;
            case 1: // Burning Crusade
                return 1;
            case 2: // WotLK
                return 2;
            default: // WotLK
                return 2;
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: get_account_count()
| ---------------------------------------------------------------
|
| This methods returns the number of accounts in the accounts table.
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
| This methods returns the number of accounts in the accounts table.
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
| This methods returns the number of accounts that havent logged
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
| This methods returns the number of accounts that have logged
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

/* 
| -------------------------------------------------------------- 
| Account Object
| --------------------------------------------------------------
|
| Author:       Wilson212
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
*/
class Account
{
    // Our Parent wowlib class and Database connection
    protected $DB;
    protected $parent;
    
    // Have we changed our username? If so, we must have set a password!
    protected $changed = false;
    
    // Our temporary password when the setPassword method is called
    protected $password;
    
    // Account ID and User data array
    protected $id;
    protected $data = array();
/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($acct, $parent)
    {
        // Load the realm database connection
        $this->load = load_class('Loader');
        $this->DB = $this->load->database('RDB');
        
        // Setup local user variables
        $this->id = $acct;
        $this->parent = $parent;
        
        // Load the user
        // Check the Realm DB for this username
        $query = "SELECT
            `username`,
            `sha_pass_hash`,
            `sessionkey`,
            `v`,
            `s`,
            `email`,
            `joindate`,
            `last_ip`,
            `locked`,
            `last_login`,
            `expansion`
            FROM `account` WHERE `id`= ?";
        $this->data = $this->DB->query( $query, array($acct) )->fetch_row();
        
        // If the result is NOT false, we have a match, username is taken
        if(!is_array($this->data)) throw new \Exception('User Doesnt Exist');
    }
    
/*
| ---------------------------------------------------------------
| Method: save()
| ---------------------------------------------------------------
|
| This method saves the current account data in the database
|
| @Retrun: (Bool): If the save is successful, returns TRUE
|
*/ 
    public function save()
    {
        // First we have to check if the username was changed
        if($this->changed)
        {
            if(empty($this->password)) return false;
            
            // Make sure the sha hash is set correctly
            $this->setPassword($this->password);
        }
        
        return ($this->DB->update('account', $this->data, "`id`= $this->id") !== false);
    }
    
/*
| ---------------------------------------------------------------
| Method: getId()
| ---------------------------------------------------------------
|
| This method returns the account id
|
| @Return (Int)
|
*/
    public function getId()
    {
        return (int) $this->id;
    }
    
/*
| ---------------------------------------------------------------
| Method: getUsername()
| ---------------------------------------------------------------
|
| This method returns the account username
|
| @Return (String)
|
*/
    public function getUsername()
    {
        return $this->data['username'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getUsername()
| ---------------------------------------------------------------
|
| This method returns the account email address
|
| @Return (String)
|
*/
    public function getEmail()
    {
        return $this->data['email'];
    }
    
/*
| ---------------------------------------------------------------
| Method: joinDate()
| ---------------------------------------------------------------
|
| This method returns the joindate for this account
|
| @Return (mixed)
|
*/
    public function joinDate($asTimestamp = false)
    {
        return ($asTimestamp == true) ? strtotime($this->data['joindate']) : $this->data['joindate'];
    }
    
/*
| ---------------------------------------------------------------
| Method: lastLogin()
| ---------------------------------------------------------------
|
| This method returns the last login date / time for this account
|
| @Return (Mixed)
|
*/
    public function lastLogin($asTimestamp = false)
    {
        return ($asTimestamp == true) ? strtotime($this->data['last_login']) : $this->data['last_login'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getLastIp()
| ---------------------------------------------------------------
|
| This method returns the accounts last seen IP
|
| @Return (String)
|
*/
    public function getLastIp()
    {
        return $this->data['last_ip'];
    }
    
/*
| ---------------------------------------------------------------
| Method: isLocked()
| ---------------------------------------------------------------
|
| This method returns if the account is locked
|
| @Return (Bool)
|
*/
    public function isLocked()
    {
        return (bool) $this->data['locked'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getExpansion()
| ---------------------------------------------------------------
|
| This method returns the accounts expansion ID
|
| @Return (Int)
|
*/
    public function getExpansion($asText = false)
    {
        return ($asText == true) ? $this->parent->expansionToText($this->data['expansion']) : (int) $this->data['expansion'];
    }
    
/*
| ---------------------------------------------------------------
| Method: setPassword()
| ---------------------------------------------------------------
|
| This method sets the password to the account.
|
| @Param: (String) $password - The new account (unencrypted) password
| @Return (Bool) - Returns false only if password is less then 3 chars.
|
*/
    public function setPassword($password)
    {
        // Remove whitespace in password
        $password = trim($password);
        if(strlen($password) < 3) return false;
        
        // Set our passwords
        $this->password = $password;
        $this->data['sha_pass_hash'] = sha1( strtoupper($this->data['username'] .':'. $password) );
        $this->data['sessionkey'] = null;
        $this->data['v'] = null;
        $this->data['s'] = null;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setUsername()
| ---------------------------------------------------------------
|
| This method sets the username to the account.
|
| @Param: (String) $username - The new account username / login
| @Return (Bool) - Returns false only if username is less then 3 chars.
|
*/
    public function setUsername($username)
    {
        // Remove whitespace
        $username = trim($username);
        if(strlen($username) < 3) return false;
        
        // Set our username if its not the same as before
        if($username != $this->data['username'])
        {
            $this->changed = true;
            $this->data['username'] = $username;
            return true;
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: setEmail()
| ---------------------------------------------------------------
|
| This method sets an accounts email address
|
| @Return (None)
|
*/
    public function setEmail($email)
    {
        $this->data['email'] = $email;
    }
    
/*
| ---------------------------------------------------------------
| Method: setExpansion()
| ---------------------------------------------------------------
|
| This method sets the expansion to the account.
|
| @Param: (Int) $e - Sets the expansion level of the account
|   0 => None, Base Game
|   1 => Burning Crusade
|   2 => WotLK
|   3 => Cata (If Supported)
|   4 => MoP (If Supported)
| @Return (None)
|
*/
    public function setExpansion($e)
    {
        $this->data['expansion'] = $this->parent->expansionToBit($e);
    }
    
/*
| ---------------------------------------------------------------
| Method: setLocked()
| ---------------------------------------------------------------
|
| This method sets the locked status of an account
|
| @Return (None)
|
*/
    public function setLocked($locked)
    {
        // Set to an integer
        $this->data['locked'] = ($locked == true) ? 1 : 0;
    }
}
// EOF