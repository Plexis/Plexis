<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Wowlib/Wowlib.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @package     Wowlib
 * @contains    Wowlib
 */
namespace Wowlib;

// Bring some Plexis CMS core classes into scope
use \Core\Autoloader;
use \Database\Driver as DBDriver;
use \Core\Filesystem;
use \Exception;

/**
 * WowLib Framework for WoW Private Server CMS
 *
 * @author      Steven Wilson
 * @package     Wowlib
 */
class Wowlib
{
    /**
     * Contains the wowlib version. This constant only changes when the wowlib has a massive update,
     * or makes a change, that could cause drivers to not be fully compatible anymore.
     */
    const VERSION = '3.0';
    
    /**
     * Contains the wowlib revision. This number changes with each wowlib update, but only reflects
     * minor changes, that will not affect the wowlib drivers in any way.
     */
    const REVISION = 1;
    
    /**
     * Emulator string name
     * @var string
     */
    protected static $emulator = '';
    
    /**
     * Internal var for knowing if the wowlib is initialized
     * @var bool
     */
    protected static $initilized = false;
    
    /**
     * Array of loaded realm instances
     * @var \Wowlib\Emulator[]
     */
    protected static $realm = array();
    
    /**
     * Initializes the wowlib with the provided emulator name
     *
     * @param string $emulator The name of the emulator to load
     *
     * @return void
     */
    public static function Init($emulator = '')
    {
        // Init the wowlib jsut once
        if(self::$initilized) return;

        // Set emulator paths, and scan to see which emulators exist
        $emulator = strtolower($emulator);
        if(!is_dir( path(WOWLIB_ROOT, 'library', $emulator) ))
            throw new \Exception('Emulator '. $emulator .' not found in the emulators folder.');
        
        // Set the emulator
        self::$emulator = $emulator;
        
        // Register our namespaces and interface paths
        AutoLoader::RegisterNamespace('Wowlib', 
            array(
                path( WOWLIB_ROOT, 'interfaces' ),
                path( WOWLIB_ROOT, 'library' ),
            )
        );
        
        // Set that we are initialized, and get our init time for benchmarking
        self::$initilized = true;
    }
    
    /**
     * Fetches or initiates a new emulator object with the provided realm id
     *
     * This method returns a realm instance based off of the ID paramenter
     * if the realm isnt set, a new instance of the emulators realm 
     * class is initiated and returned
     *
     * @param string|int $id - The array key for this realm ID.
     *   Can be a stringname, or Integer, and is used only for when
     *   you need to use the getRealm() method.
     * @param string[] $DB An array of database connection 
     *   information as defined below. Not needed unless loading a new
     *   realm that is previously unloaded.
     *      array(
     *          'driver'
     *          'host'
     *          'port'
     *          'database'
     *          'username'
     *          'password'
     *      )
     *
     * @return \Wowlib\Emulator|bool Returns false if the emulator couldnt be loaded
     */
    public static function GetRealm($id = 0, $DB = array())
    {
        // Make sure we are loaded here!
        if(!self::$initilized) 
            throw new Exception('Cannot set emulator, Wowlib was never initialized!');
        
        // If this realm is already set, then just return this realm
        if(isset(self::$realm[$id])) 
            return self::$realm[$id];
        
        // Make sure we have DB conection info
        if(empty($DB)) 
            throw new Exception('No Database information supplied. Unable to load realm.');
        
        // Set our Emu var to the current selected emulator
        $ucEmu = ucfirst(self::$emulator);
        
        // Check for custom emulator class
        $class = "\\Wowlib\\Emulator";
        $file = path( WOWLIB_ROOT, 'library', self::$emulator, $ucEmu .'.php' );
        if(file_exists($file))
        {
            require_once $file;
            $class = "\\Wowlib\\". $ucEmu;
        }
        elseif(!class_exists($class, false))
        {
            require path( WOWLIB_ROOT, 'library', 'Emulator.php' );
        }
        
        // Init the realm class
        try {
            $DB = new DBDriver($DB);
            self::$realm[$id] = new $class( self::$emulator, $DB );
        }
        catch( \Exception $e) {
            self::$realm[$id] = false;
        }

        return self::$realm[$id];
    }
    
    /**
     * This method is used to load, and return a new instance of a Driver
     *
     * @param string $driver The driver name to load
     * @param string[] $char An array of database connection for the characters database
     * @param string[] $world An array of database connection for the world database
     *
     * @return \Wowlib\Driver
     */
    public static function Load($driver, $char, $world)
    {
        // Make sure we are loaded here!
        if(!self::$initilized) 
            throw new Exception('Cannot load driver, Wowlib was never initialized!', 1);
        
        // Load a new instance of the Driver class
        return new Driver(self::$emulator, $driver, $char, $world);
    }
    
    /**
     * Returns an array of all the available drivers for the selected emulator
     *
     * @return string[]
     */
    public static function GetDrivers()
    {
        // List all the drivres in the emulator folder.
        $path = path( WOWLIB_PATH, 'drivers', self::$emulator );
        return Filesystem::ListFolders($path);
    }
    
    /**
     * Sets the emulator name for all future drivers
     *
     * @param string $emu The name of the emulator.
     *
     * @return bool Returns false if the emulator doesnt exist,
     *   true otherwise.
     */
    public static function SetEmulator($emu)
    {
        // Make sure we are loaded here!
        if(!self::$initilized) 
            throw new Exception('Cannot load driver, Wowlib was never initialized!', 1);
        
        // List all the drivres in the emulator folder.
        $emu = strtolower($emu);
        if(!is_dir( path(WOWLIB_ROOT, 'library', $emu) ))
            return false;
        
        self::$emulator = $emu;
        return true;
    }
}

// Define wowlib constants, and load the wowlib required files
define('WOWLIB_ROOT', dirname(__FILE__));
require WOWLIB_ROOT . DS .'drivers'. DS .'Driver.php';