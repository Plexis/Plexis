<?php
/* 
| --------------------------------------------------------------
| 
| WowLib Framework for WoW Private Server CMS'
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Author:       Tony Hudgins
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/

class Wowlib
{
    /*
        Constant: VERSION
        Contains the wowlib version. This constant only changes when the wowlib makes a change, 
        that could cause drivers to not be fully compatible via the interface templates (Ex:
        a new method is added to the Characters class)
    */
    const VERSION = '1.2';
    
    /*
        Constant: REVISION
        Contains the wowlib revision. This number changes with each wowlib update, but only reflects
        minor changes, that will not affect the wowlib drivers in any way.
    */
    const REVISION = 14;
    
    // Static Variables
    public static $emulator;                // Emulator string name
    public static $emulators;               // Array of installed emulators
    public static $initTime;                // Initilize time for the wowlib constructor
    protected static $initilized = false;   // Wowlib initialized?
    protected static $realm = array();      // Array of loaded realm instances
    
    // Plexis CMS Version Variables
    protected static $load;
    protected static $Fs;
    

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
| @Param: (String) $emulator - The emulator name
| @Param: (Array) $DB - An array of database connection information
|   As defined below:
|       array(
|           'driver' - Mysql, Postgres etc etc
|           'host' - Hostname
|           'port' - Port Number
|           'database' - Database name
|           'username' - Database username
|           'password' - Password to the database username
|       )
| @Return (None) - nothing is returned
|
*/
    public static function Init($emulator, $DB = array())
    {
        // Load some things just once
        if(!self::$initilized)
        {
            // Init a start time for benchmarking
            $start = microtime(1);
            
            \Debug::trace('one');

            // Set emulator paths, and scan to see which emulators exist
            $path = path( WOWLIB_ROOT, 'emulators' );
            self::$load = load_class('Loader');
            self::$Fs = self::$load->library('Filesystem');
            self::$emulators = self::$Fs->list_folders($path);
            
            // Make sure the emulator exists before defining it
            if(!is_array(self::$emulators))
                throw new Exception('Unable to open the wowlib emulators folder. Please corretly set your permissions.', 2);
            elseif(!in_array($emulator, self::$emulators))
                throw new Exception('Emulator '. $emulator .' not found in the emulators folder.', 3);
            else
                self::$emulator = strtolower($emulator);
                
            
            // Get a full list of interfaces
            $path = path( WOWLIB_ROOT, 'interfaces' );
            $list = self::$Fs->list_files($path);
            if(!is_array($list))
                throw new Exception('Unable to open the wowlib interfaces folder. Please corretly set your permissions.', 4);
                
            
            // Autload each interface so the class' dont have to
            foreach($list as $file) include path($path, $file);
            
            // Set that we are initialized
            self::$initilized = true;
            
            // If DB information was passed, then init a new realm connection
            if(!empty($DB))
            {
                try {
                    self::getRealm(0, $DB);
                }
                catch( Exception $e ) {
                    // Hush error
                }
            }
            
            // Set Init time
            self::$initTime = round( microtime(1) - $start, 5);
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: getRealm
| ---------------------------------------------------------------
|
| This method returns a realm instance based off of the ID paramenter
|   if the realm isnt set, a new instance of the emulators realm 
|   class is initiated and returned
|
| @Param: (String | Int) $id - The array key for this realm ID.
|   Can be a stringname, or Integer, and is used only for when
|   you need to use the getRealm() method.
| @Params: (Array) $DB - An array of database connection 
|   information as defined below. Not needed unless loading a new
|   realm that is previously unloaded
|       array(
|           'driver' - Mysql, Postgres etc etc
|           'host' - Hostname
|           'port' - Port Number
|           'database' - Database name
|           'username' - Database username
|           'password' - Password to the database username
|       )
| @Param: (String) $emu - Specify's which emulator realm we want,
|   without having to change the emulator
| @Return (Object) - false if object failed to load
|
*/
    public static function getRealm($id = 0, $DB = array(), $emu = null)
    {
        // Make sure we are loaded here!
        if(!self::$initilized) throw new Exception('Cannot set emulator, Wowlib was never initialized!', 1);
        
        // If this realm is already set, then just return this realm
        if(isset(self::$realm[$id])) return self::$realm[$id];
        
        // Make sure we have DB conection info
        if(empty($DB)) throw new Exception('No Database information supplied. Unable to load realm.');
        
        // Check if the user is quick changing realms
        if(!empty($emu) && !in_array($emu, self::$emulators)) return false;
        
        // Load the emulator class
        if(empty($emu)) $emu = self::$emulator;
        $ucEmu = ucfirst($emu);
        $class = "\\Wowlib\\". $ucEmu;
        
        // Check and see of the class is already loaded
        if(!class_exists($class, false))
        {
            $file = path( WOWLIB_ROOT, 'emulators', $emu, $ucEmu .'.php' );
            if(!file_exists($file)) return false;
            require $file;
        }
        
        // Init the realm class
        try {
            $DB = self::$load->database($DB);
            self::$realm[$id] = new $class( $DB );
        }
        catch( \Exception $e) {
            self::$realm[$id] = false;
        }

        return self::$realm[$id];
    }
    
/*
| ---------------------------------------------------------------
| Method: load
| ---------------------------------------------------------------
|
| This method is used to load, and return a new instance of a Driver
|
| @Param: (String) $driver - The driver name
| @Params: (Array) $char && $world - An array of database connection 
|   information as defined below:
|       array(
|           'driver' - Mysql, Postgres etc etc
|           'host' - Hostname
|           'port' - Port Number
|           'database' - Database name
|           'username' - Database username
|           'password' - Password to the database username
|       )
| @Return (Object) - false if object failed to load
|
*/
    public static function load($driver, $char, $world)
    {
        // Make sure we are loaded here!
        if(!self::$initilized) throw new Exception('Cannot load driver, Wowlib was never initialized!', 1);
        
        // Load a new instance of the Driver class
        return new \Wowlib\Driver(self::$emulator, $driver, $char, $world);
    }
    
/*
| ---------------------------------------------------------------
| Method: getDrivers
| ---------------------------------------------------------------
|
| @Return (Array) - Returns an array of all available drivers for
|   the selected emulator
|
*/
    public static function getDrivers()
    {
        // Make sure we are loaded here!
        if(!self::$initilized) throw new Exception('Cannot load driver, Wowlib was never initialized!', 1);
        
        // List all the drivres in the emulator folder.
        $path = path( WOWLIB_PATH, 'drivers', self::$emulator );
        return self::$Fs->list_folders($path);
    }
    
/*
| ---------------------------------------------------------------
| Method: setEmulator
| ---------------------------------------------------------------
|
| @Return (Bool) - Returns false if the emulator was not found,
|   true otherwise
|
*/
    public static function setEmulator($emu)
    {
        // Make sure we are loaded here!
        if(!self::$initilized) throw new Exception('Cannot load driver, Wowlib was never initialized!', 1);
        
        // List all the drivres in the emulator folder.
        if(!in_array($emu, self::$emulators)) return false;
        self::$emulator = $emu;
        return true;
    }
}

// Define wowlib constants, and load the wowlib required files
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if(!defined('WOWLIB_ROOT')) define('WOWLIB_ROOT', dirname(__FILE__));
require WOWLIB_ROOT . DS .'drivers'. DS .'Driver.php';
?>