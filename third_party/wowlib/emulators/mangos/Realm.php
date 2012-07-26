<?php
/* 
| -------------------------------------------------------------- 
| Mangos Realm Object
| --------------------------------------------------------------
|
| Author:       Wilson212
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/

// All namespace paths are uppercase first. Format: Wowlib\<Emulator>;
namespace Wowlib\Mangos;

class Realm implements \Wowlib\iRealm
{
    // Our Parent wowlib class and Database connection
    protected $DB;
    protected $parent;
    
    // Account ID and User data array
    protected $data = array();
/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($id, $parent)
    {
        // Load the realm database connection
        $this->DB = $parent->DB;
        
        // Load the user
        // Check the Realm DB for this username
        $query = "SELECT
            `id`,
            `name`,
            `address`,
            `port`,
            `icon`,
            `flag`,
            `population`,
            `gamebuild`
            FROM `realmlist` WHERE `id`= ?";
        $this->data = $this->DB->query( $query, array($id) )->fetchRow();
        
        // If the result is NOT false, we have a match, username is taken
        if(!is_array($this->data)) throw new \Exception('Realm Doesnt Exist');
    }
    
/*
| ---------------------------------------------------------------
| Method: save()
| ---------------------------------------------------------------
|
| This method saves the current realm data in the database
|
| @Retrun: (Bool): If the save is successful, returns TRUE
|
*/ 
    public function save()
    {
        return ($this->DB->update('realmlist', $this->data, "`id`= ".$this->data['id']) !== false);
    }
    
/*
| ---------------------------------------------------------------
| Method: getName()
| ---------------------------------------------------------------
|
| This method returns the realms name
|
| @Return (String)
|
*/
    public function getName()
    {
        return $this->data['name'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getAddress()
| ---------------------------------------------------------------
|
| This method returns the realms address
|
| @Return (String)
|
*/
    public function getAddress()
    {
        return $this->data['address'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getPort()
| ---------------------------------------------------------------
|
| This method returns the realms port
|
| @Return (String)
|
*/
    public function getPort()
    {
        return $this->data['port'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getType()
| ---------------------------------------------------------------
|
| This method returns the realms type
|
| @Return (Int)
|
*/
    public function getType()
    {
        return (int) $this->data['icon'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getPopulation()
| ---------------------------------------------------------------
|
| This method returns the realms population as a float value
|
| @Return (Float) - 0.5 for low, 1.0 for medium, 2.0 for High
|
*/
    public function getPopulation()
    {
        return (float) $this->data['population'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getBuild()
| ---------------------------------------------------------------
|
| This method returns the realms game build
|
| @Return (Int)
|
*/
    public function getBuild()
    {
        return (int) $this->data['gamebuild'];
    }
    
/*
| ---------------------------------------------------------------
| Method: getStatus()
| ---------------------------------------------------------------
|
| This method returns the realms online status
|
| @Return (Int) - 1 for online, 0 for offline
|
*/
    public function getStatus($timeout = 3)
    {
        // If we have the flag of 2, then we are offline no matter what
        if((int)$this->data['flag'] == 2) return 0;
        $status = @fsockopen($this->data['address'], $this->data['port'], $err, $estr, $timeout);
        return (int)($handle !== false);
    }
    
/*
| ---------------------------------------------------------------
| Method: setName()
| ---------------------------------------------------------------
|
| This method sets the realms name
|
| @Param: (String) $name - The realms new name
| @Return (Bool)
|
*/
    public function setName($name)
    {
        if(!is_string($name) || strlen($name) > 32) return false;
        $this->data['name'] = $name;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setAddress()
| ---------------------------------------------------------------
|
| This method sets the realms address
|
| @Param: (String) $address - The realms new address
| @Return (Bool)
|
*/
    public function setAddress($address)
    {
        $this->data['address'] = $address;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setPort()
| ---------------------------------------------------------------
|
| This method returns the realms port
|
| @Param: (Int) $port - The realms new port number
| @Return (Bool)
|
*/
    public function setPort($port)
    {
        if(!is_numeric($port)) return false;
        $this->data['port'] = (int) $port;
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: setType()
| ---------------------------------------------------------------
|
| This method returns the realms type
|
| @Return (Bool) Returns false of the type passed is invalid
|
*/
    public function setType($icon)
    {
        $i = (int) $icon;
        if($i != 0 || $i != 1 || $i != 4 || $i != 6 || $i != 8) return false;
        $this->data['icon'] = $i;
    }
}