<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Wowlib/Drivers/Driver.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @package     Wowlib
 * @contains    Driver
 */
namespace Wowlib;

// Bring some Plexis CMS core classes into scope
use \Database\Driver as DBDriver;

/**
 * This class is used to handle different drivers of all different
 * emulators
 *
 * @author      Steven Wilson
 * @package     Wowlib
 */
class Driver
{
    /**
     * Character database object
     * @var \Database\Driver
     */
    protected $CDB;
    
    /**
     * World database object
     * @var \Database\Driver
     */
    protected $WDB;
    
    /**
     * Driver configuration array
     * @var mixed[]
     */
    protected $config;
    
    /**
     * Loaded driver name
     * @var string
     */
    protected $driver;
    
    /**
     * Selected emulator name
     * @var string
     */
    protected $emulator;
    
    /**
     * Array of loaded driver extensions
     * @var object[]
     */
    protected $loaded = array();
    
    
    /**
     * Constructor
     *
     * @param string $emulator The name of the selected emulator
     * @param string $driver The name of the driver to load
     * @param string[] $char An array of database connection for the characters database
     * @param string[] $world An array of database connection for the world database
     *
     * @return void
     */
    public function __construct($emulator, $driver, $char, $world)
    {
        // First, we must load the driver config!
		$file = path( WOWLIB_ROOT, 'drivers', strtolower($emulator), strtolower($driver), 'Driver.php' );
        
        // If extension doesnt exist, return false
        if(!file_exists($file)) 
            throw new \Exception('Config file for driver '. $driver .' not found');
            
        require $file;
        
        // Load the config variables into a local variable
        $this->config = $config;
        $this->CDB = false;
        $this->WDB = false;
        
        // Load the character DB
        if(is_array($char))
        {
            try {
                $this->CDB = new DBDriver($char);
            }
            catch(\Exception $e) {
                $this->CDB = false;
            }
        }
        
        // Load world DB
        if(is_array($world))
        {
            try {
                $this->WDB = new DBDriver($world);
            }
            catch(\Exception $e) {
                $this->WDB = false;
            }
        }

        // Finally set our emulator and driver variables
        $this->emulator = $emulator;
        $this->driver = $driver;
    }
    
    /**
     * Returns if the namespaced driver extension class exists, and has
     * been loaded
     *
     * @param string $name The driver classname
     * @param bool $include If set to false, the class will not be loaded,
     *   and this method will true if the class file exists only.
     *
     * @return bool
     */
    public function driverHasExt($name, $include = true)
    {
        $name = ucfirst( strtolower($name) );
        $driver = strtolower($this->driver);
        
        // Make sure if isnt loaded already
        if(in_array($name, $this->loaded)) 
            return $this->loaded[$name];
        
        // Check for the extension
		$file = path( WOWLIB_ROOT, 'drivers', $this->emulator, $driver, $name .'.php' );
        
        // If extension doesnt exist, return false
        if(!file_exists($file))
        {
            $this->loaded[$name] = false;
            return false;
        }
        
        // Load the file if $include is true
        if($include)
        {
            // Set the variable
            $this->loaded[$name] = true;
            require $file;
        }
        
        // return success
        return true;
    }
    
    /**
     * Returns the drivers config array
     *
     * @return string[]
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * Returns the column name for the given table id
     *
     * @param string $table The table ID
     * @param string $col The table column ID
     *
     * @return string|bool
     */
    public function getColumnById($table, $col)
    {
        // Make sure the config key exists
        return (isset($this->config["{$table}Columns"][$col])) 
            ? $this->config["{$table}Columns"][$col] 
            : false;
    }
    
    /**
     * Returns the namespace to all driver classes
     *
     * @return string
     */
    public function getDriverNamespace()
    {
        return "\\Wowlib\\". ucfirst($this->emulator) ."\\". ucfirst($this->driver);
    }
    
    /**
     * Returns the character database connection object
     *
     * @return \Database\Driver|bool Return false only if the
     *   character database connection fails
     */
    public function getCDB()
    {
        return $this->CDB;
    }
    
    /**
     * Returns the world database connection object
     *
     * @return \Database\Driver|bool Return false only if the
     *   world database connection fails
     */
    public function getWDB()
    {
        return $this->WDB;
    }
    
    /**
     * Magic method used to automatically load called extensions
     *
     * @param string $name The name of the requested extension
     *
     * @return object|bool
     */
    public function __get($name)
    {
        // Just return the extension if it exists
        $name = strtolower($name);
        if(isset($this->{$name}))
            return $this->{$name};
        
        // Create our classname
        $class = ucfirst($name);
        $driver = strtolower($this->driver);
        $ucEmu = ucfirst($this->emulator);
        
        // Check for the extension
		$file = path( WOWLIB_ROOT, 'library', $class .'.php' );
        
        // If extension doesnt exist, return false
        if(!file_exists($file)) 
            return false;
        
        // Load the extension file
        require_once($file);
        
        // Load the class
        $class = "\\Wowlib\\{$class}";
        try {
            $this->{$name} = new $class($this);
        }
        catch(\Exception $e) {
            $this->{$name} = false;
        }
        return $this->{$name};
    }
}
?>