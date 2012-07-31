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
    
    // Our config and column names
    protected $config = array();
    protected $cols = array();
    
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
    public function __construct($data, $parent)
    {
        // If the result is NOT false, we have a match, username is taken
        if(!is_array($data)) throw new \Exception('Account Doesnt Exist');
        
        // Get Our realm DB Connection
        $this->DB = $parent->getDB();
        
        // Setup local user variables
        $this->parent = $parent;
        $this->data = $data;
        
        // Get our array of columns
        $this->config = $this->parent->getConfig();
        $this->cols = $this->config['accountColumns'];
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
        
        // Fetch our table name, and ID column for the query
        $table = $this->config['accountTable'];
        $col = $this->cols['id'];
        
        return ($this->DB->update($table, $this->data, "`{$col}`= ". $this->data[$col]) !== false);
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
        // Fetch our column name
        $col = $this->cols['id'];
        return (int) $this->data[$col];
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
        // Fetch our column name
        $col = $this->cols['username'];
        return $this->data[$col];
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
        // Fetch our column name
        $col = $this->cols['email'];
        if(!$col) return false;
        
        return $this->data[$col];
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
        // Fetch our column name
        $col = $this->cols['joindate'];
        if(!$col) return false;
        
        return ($asTimestamp == true) ? strtotime($this->data[$col]) : $this->data[$col];
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
        // Fetch our column name
        $col = $this->cols['lastLogin'];
        if(!$col) return false;
        
        return ($asTimestamp == true) ? strtotime($this->data[$col]) : $this->data[$col];
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
        // Fetch our column name
        $col = $this->cols['lastIp'];
        if(!$col) return false;
        
        return $this->data[$col];
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
        // Fetch our column name
        $col = $this->cols['locked'];
        if(!$col) return false;
        
        return (bool) $this->data[$col];
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
        // We need to convert the bit value to normal
        $exp = $this->data[ $this->cols['expansion'] ];
        $val = array_search($exp, $this->config['expansionToBit']);
        
        return ($asText == true) ? expansionToText($val) : (int) $val;
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
        if($this->cols['shaPassword']) 
            $this->data[ $this->cols['shaPassword'] ] = sha1(strtoupper($this->data['username'] .':'. $password));
        if($this->cols['password']) $this->data[ $this->cols['password'] ] = $password;
        if($this->cols['sessionkey']) $this->data[ $this->cols['sessionkey'] ] = null;
        if($this->cols['v']) $this->data[ $this->cols['v'] ] = null;
        if($this->cols['s']) $this->data[ $this->cols['s'] ] = null;
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
            $this->data[ $this->cols['username'] ] = $username;
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
        if(!$this->cols['email']) return false;
        $this->data[ $this->cols['email'] ] = $email;
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
        if(!$this->cols['expansion']) return false;
        $this->data[ $this->cols['expansion'] ] = $this->parent->expansionToBit($e);
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
        // Get our column name
        $col = $this->cols['locked'];
        if(!$col) return false;
        
        // Set to an integer
        $this->data[ $col ] = ($locked == true) ? 1 : 0;
    }
}