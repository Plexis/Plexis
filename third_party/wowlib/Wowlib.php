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

// Kill the script if its a direct link!
if( !defined('CMS_VERSION') ) die('Unauthorized');

class Wowlib
{
    // Our DB Connections and loader
    public static $RDB;
    protected static $load;
    
    // Static Instances
    public static $rootPath = false;
    public static $emulator;
    protected static $realm;
    

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public static function Init()
    {
        // Load some things just once
        if(self::$rootPath === false)
        {
            self::$load = load_class('Loader');
            
            // Set path to prevent future loading
            self::$rootPath = path( ROOT, 'third_party', 'wowlib' );
            
            // Set the connections into the connection variables
            self::$RDB = self::$load->database('RDB', false, true);
            
            // Set Emulator Variable
            self::$emulator = load_class('Config')->get('emulator');
            
            // Autoload
            $path = path( self::$rootPath, 'interfaces' );
            $list = self::$load->library('Filesystem')->list_files($path);
            foreach($list as $interface)
            {
                include ($path . DS . $interface);
            }
            
            // Load the emulator, and the driver class
            require_once path(self::$rootPath, 'drivers', 'Driver.php');
            $file = path(self::$rootPath, 'emulators', self::$emulator, 'Emulator.php');
            if(!file_exists($file)) throw new Exception("Emulator '". self::$emulator ."' Doesnt Exist");
            require_once $file;
            
            // Init the realm class
            try {
                self::$realm = new \Wowlib\Emulator( self::$RDB );
            }
            catch( \Exception $e) {
                self::$realm = false;
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| Driver Loader
| ---------------------------------------------------------------
|
*/
    public static function load($driver, $char = null, $world = null)
    {
        // Make sure we are loaded here!
        if(self::$rootPath === false) throw new Exception('Cannot load driver, Wowlib was never initialized!');
        
        // Load a new instance of the Driver class
        return new \Wowlib\Driver($driver, $char, $world);
    }
    
/*
| ---------------------------------------------------------------
| Realm Loader
| ---------------------------------------------------------------
|
*/
    public static function getRealm()
    {
        // Make sure we are loaded here!
        if(self::$rootPath === false) throw new Exception('Cannot fetch realm, Wowlib was never initialized!');
        return self::$realm;
    }
}
?>