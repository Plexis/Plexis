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

// Kill the script if its a direct link!
if( !defined('CMS_VERSION') ) die('Unauthorized');

class Wowlib
{
    // Wowlib Constants
    const VERSION = '1.0';
    
    // Our DB Connections and loader
    public static $RDB;
    protected static $load;
    
    // Static Instances
    public static $emulator;
    public static $rootPath;
    protected static $initilized = false;
    protected static $realm = array();
    

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public static function Init()
    {
        // Load some things just once
        if(!self::$initilized)
        {
            // Define our root dir
            self::$rootPath = dirname(__FILE__);
            
            // Load the loader, and database connection
            self::$load = load_class('Loader');
            self::$RDB = self::$load->database('RDB', false, true);
            
            // Set Emulator Variable
            self::$emulator = load_class('Config')->get('emulator');
            $ucEmu = ucfirst(self::$emulator);
            
            // Autoload Interfaces
            $path = path( self::$rootPath, 'interfaces' );
            $list = self::$load->library('Filesystem')->list_files($path);
            foreach($list as $interface)
            {
                include ($path . DS . $interface);
            }
            
            // Load the emulator, and the driver class
            require_once path(self::$rootPath, 'drivers', 'Driver.php');
            $file = path(self::$rootPath, 'emulators', self::$emulator, $ucEmu .'.php');
            if(!file_exists($file)) throw new Exception("Emulator '". self::$emulator ."' Doesnt Exist");
            require_once $file;
            
            // Init the realm class
            try {
                $class = "\\Wowlib\\". $ucEmu;
                self::$realm[self::$emulator] = new $class( self::$RDB );
            }
            catch( \Exception $e) {
                self::$realm[self::$emulator] = false;
            }
            
            // Set that we are initialized
            self::$initilized = true;
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
        if(!self::$initilized) throw new Exception('Cannot load driver, Wowlib was never initialized!');
        
        // Load a new instance of the Driver class
        return new \Wowlib\Driver(self::$emulator, $driver, $char, $world);
    }
    
/*
| ---------------------------------------------------------------
| Realm Loader
| ---------------------------------------------------------------
|
*/
    public static function getRealm($emu = null)
    {
        // Make sure we are loaded here!
        if(!self::$initilized) throw new Exception('Cannot fetch realm, Wowlib was never initialized!');
        
        // If we have specified an emulator, load it, and return it
        if($emu != null)
        {
            if(!isset(self::$realm[$emu]))
            {
                // Load the emulator class
                $ucEmu = ucfirst($emu);
                $file = path(self::$rootPath, 'emulators', $emu, $ucEmu .'.php');
                if(!file_exists($file)) return false;
                require_once $file;
                
                // Init the realm class
                try {
                    $class = "\\Wowlib\\". $ucEmu;
                    self::$realm[$emu] = new $class( self::$RDB );
                }
                catch( \Exception $e) {
                    self::$realm[$emu] = false;
                }
            }
            return self::$realm[$emu];
        }
        return self::$realm[self::$emulator];
    }
    
/*
| ---------------------------------------------------------------
| Emulator Setter
| ---------------------------------------------------------------
|
*/
    public static function setEmulator($emu)
    {
        // Make sure we are loaded here!
        if(!self::$initilized) throw new Exception('Cannot set emulator, Wowlib was never initialized!');
        
        // Set Emulator Variable
        self::$emulator = strtolower($emu);
    }
}
?>