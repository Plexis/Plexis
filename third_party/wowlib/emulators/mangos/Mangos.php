<?php
/* 
| --------------------------------------------------------------
| 
| WowLib Framework for WoW Private Server CMS'
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/
namespace Wowlib;

class Mangos implements iEmulator
{
    // Our DB Connection
    public $DB;

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($DB)
    {
        // Set local variables
        $this->DB = $DB;
        
        // Load our extensions needed
        $root = \Wowlib::$rootPath;
        require_once path($root, 'emulators', 'mangos', 'Account.php');
        require_once path($root, 'emulators', 'mangos', 'Realm.php');
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
        return $this->DB->query( $query )->fetchAll();
    }
    
/*
| ---------------------------------------------------------------
| Method: fetchRealm()
| ---------------------------------------------------------------
|
| This function gets the realm ID into an object
|
| @Param: (Int) $id - The realm ID we are requesting the information from
| @Return (Object) - Returns the RealmId Object, or false on failure
|
*/
    public function fetchRealm($id)
    {
        try {
            $realm = new Realm($id, $this);
        }
        catch (\Exception $e) {
            $realm = false;
        }
        return $realm;
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
        $result = $this->DB->query( $query, array($id) )->fetchColumn();
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
        return ($this->DB->numRows() > 0) ? $this->DB->lastInsertId() : false;
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
| @Return (Bool) - Returns TRUE on success, FALSE otherwise
|
*/
    public function validate($username, $password)
    {
        // SHA1 the password
        $user = strtoupper($username);
        $pass = strtoupper($password);
        $password = sha1($user.':'.$pass);
        
        // Load the users info from the Realm DB
        $query = "SELECT `id`, `sha_pass_hash` FROM `account` WHERE `username`=?";
        $result = $this->DB->query( $query, array($username) )->fetchRow();
        
        // Make sure the username exists!
        if(!is_array($result)) return false;
        
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
        $res = $this->DB->query( $query, array($id) )->fetchColumn();
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
        $res = $this->DB->query( $query, array($email) )->fetchColumn();
        
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
        $check = $this->DB->query( $query, array($account_id) )->fetchColumn();
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
        $check = $this->DB->query( $query, array($ip) )->fetchColumn();
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
        $ip = $this->DB->query( $query, array($id) )->fetchColumn();
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
        $ip = $this->DB->query( $query, array($id) )->fetchColumn();
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
| Function: numAccounts()
| ---------------------------------------------------------------
|
| This methods returns the number of accounts in the accounts table.
|
| @Return (Int) The number of accounts
|
*/
    
    public function numAccounts()
    {
        return $this->DB->query("SELECT COUNT(`id`) FROM `account`")->fetchColumn();
    }
    
/*
| ---------------------------------------------------------------
| Function: numBannedAccounts()
| ---------------------------------------------------------------
|
| This methods returns the number of accounts in the accounts table.
|
| @Return (Int) The number of accounts
|
*/
    
    public function numBannedAccounts()
    {
        return $this->DB->query("SELECT COUNT(`id`) FROM `account_banned` WHERE `active` = 1")->fetchColumn();
    }
    
/*
| ---------------------------------------------------------------
| Function: numInactiveAccounts()
| ---------------------------------------------------------------
|
| This methods returns the number of accounts that havent logged
|   in withing the last 3 months
|
| @Return (Int) The number of accounts
|
*/
    
    public function numInactiveAccounts()
    {
        // 90 days or older
        $time = time() - 7776000;
        $query = "SELECT COUNT(`id`) FROM `account` WHERE UNIX_TIMESTAMP(`last_login`) <  $time";
        return $this->DB->query( $query )->fetchColumn();
    }
    
/*
| ---------------------------------------------------------------
| Function: numActiveAccounts()
| ---------------------------------------------------------------
|
| This methods returns the number of accounts that have logged
|   in withing the last 24 hours
|
| @Return (Int) The number of accounts
|
*/
    
    public function numActiveAccounts()
    {
        // 90 days or older
        $time = date("Y-m-d H:i:s", time() - 86400);
        $query = "SELECT COUNT(`id`) FROM `account` WHERE `last_login` BETWEEN  '$time' AND NOW()";
        return $this->DB->query( $query )->fetchColumn();
    }
}
// EOF