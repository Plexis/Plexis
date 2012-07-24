<?php
/* 
| -------------------------------------------------------------- 
| Account Object
| --------------------------------------------------------------
|
| Author:       Wilson212
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/
namespace Wowlib;

class Account implements iAccount
{
    // Our Parent wowlib class and Database connection
    protected $DB;
    protected $parent;
    
    // Have we changed our username? If so, we must have set a password!
    protected $changed = false;
    
    // Our temporary password when the setPassword method is called
    protected $password;
    
    // User data array
    protected $data = array();
    
/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($acct, $parent)
    {
        // Get Our realm DB Connection
        $this->DB = $parent->DB;
        
        // Setup local user variables
        $this->parent = $parent;
        
        // Prepare the column name for the WHERE statement based off of $acct type
        $col = (is_numeric($acct)) ? 'id' : 'username';
        
        // Load the user
        // Check the Realm DB for this username
        $query = "SELECT
            `id`,
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
            FROM `account` WHERE `{$col}`= ?";
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
        
        return ($this->DB->update('account', $this->data, "`id`= ". $this->data['id']) !== false);
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
        return (int) $this->data['id'];
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