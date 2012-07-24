<?php
/* 
| --------------------------------------------------------------
| 
| Plexis
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/

// All namespace paths must be Uppercase first letter! Format: "Wowlib\<wowlib_name>"
namespace Wowlib\_default;

class World
{
    // Our DB Connection
    public $DB;
    
/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($parent)
    {
        // If the characters database is offline, throw an exception!
        if(!is_object($parent->WDB)) throw new \Exception('World database offline');
        
        // Set our database conntection
        $this->DB = $parent->WDB;
    }
    
/*
| ---------------------------------------------------------------
| Method: fetchItem
| ---------------------------------------------------------------
|
| This method returns an Item object of the item ID passed
|
| @Param: (String) $id - The items entry ID
|
*/     
    public function fetchItem($id)
    {
        // Build our query
        $query = "SELECT `name`, `displayid`, `quality`, `itemlevel`, `requiredlevel` FROM `item_template` WHERE `entry`=?";
        $item = $this->DB->query( $query, array($id) )->fetch_row();
        return (is_array($item)) ? $item : false;
    }
}