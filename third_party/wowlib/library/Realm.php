<?php
/* 
| -------------------------------------------------------------- 
| Realm Object
| --------------------------------------------------------------
|
| Author:       Wilson212
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/
namespace Wowlib;

class Realm implements iRealm
{
    // Our Parent wowlib class and Database connection
    protected $DB;
    protected $parent;
    protected $config;
    
    // Realm data array
    protected $data = array();
    
    // Array of coulmns
    protected $cols = array();
    
/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($data, $parent)
    {
        // If the result is NOT false, we have a match, username is taken
        if(!is_array($data)) throw new \Exception('Realm Doesnt Exist');
        
        // Load the realm database connection
        $this->DB = $parent->getDB();
        $this->data = $data;
        
        // Get our array of columns
        $this->config = $this->parent->getConfig();
        $this->cols = $this->config['realmColumns'];
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
        // Fetch our table name, and ID column for the query
        $table = $this->config['realmTable'];
        $col = $this->cols['id'];
        return ($this->DB->update($table, $this->data, "`{$col}`= ".$this->data[$col]) !== false);
    }
    
/*
| ---------------------------------------------------------------
| Method: getId()
| ---------------------------------------------------------------
|
| This method returns the realms id
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
        // Fetch our column name
        $col = $this->cols['name'];
        return $this->data[$col];
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
        // Fetch our column name
        $col = $this->cols['address'];
        return $this->data[$col];
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
        // Fetch our column name
        $col = $this->cols['port'];
        return $this->data[$col];
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
        // Fetch our column name
        $col = $this->cols['type'];
        return (int) $this->data[$col];
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
        // Fetch our column name
        $col = $this->cols['population'];
        return (float) $this->data[$col];
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
        // Fetch our column name
        $col = $this->cols['gamebuild'];
        return (int) $this->data[$col];
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
        // Fetch our column names
        $add = $this->cols['address'];
        $port = $this->cols['port'];
        
        // Check status
        $status = @fsockopen($this->data[$add], $this->data[$port], $err, $estr, $timeout);
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
        $this->data[ $this->cols['name'] ] = $name;
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
        $this->data[ $this->cols['address'] ] = $address;
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
        $this->data[ $this->cols['port'] ] = (int) $port;
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
        $this->data[ $this->cols['type'] ] = $i;
    }
}