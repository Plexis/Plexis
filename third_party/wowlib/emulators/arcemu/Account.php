<?php
/* 
| -------------------------------------------------------------- 
| Account Object
| --------------------------------------------------------------
|
| Author:       Steven Wilson
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
        $col = (is_numeric($acct)) ? 'acct' : 'login';
        
        // Load the user
        // Check the Realm DB for this username
        $query = "SELECT
            `acct`,
            `login`,
            `password`,
            `encrypted_password`,
            `banned`,
            `email`,
            `lastip`,
            `locked`,
            `lastlogin`,
            `flags`
            FROM `accounts` WHERE `{$col}`= ?";
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
        
        return ($this->DB->update('accounts', $this->data, "`acct`= ". $this->data['acct']) !== false);
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
        return (int) $this->data['acct'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getUsername()
| ---------------------------------------------------------------
|
| This method returns the account login
|
| @Return (String)
|
*/
    public function getUsername()
    {
        return $this->data['login'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getEmail()
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
        // Arcemu does not support this
        return false;
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
        return ($asTimestamp == true) ? strtotime($this->data['lastlogin']) : $this->data['lastlogin'];
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
        return $this->data['lastip'];
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
        return (bool) $this->data['banned'];
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
        $id = $this->bitToExpansion($this->data['flags']);
        return ($asText == true) ? $this->parent->expansionToText($id) : $id;
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
        $this->data['encrypted_password'] = sha1( strtoupper($this->data['login'] .':'. $password) );
        $this->data['password'] = $password;
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
        if($username != $this->data['login'])
        {
            $this->changed = true;
            $this->data['login'] = $username;
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
        $this->data['flags'] = $this->parent->expansionToBit($e);
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
        // Arcemu doesnt support this
        return false;
    }
}