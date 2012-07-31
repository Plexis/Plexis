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

// All namespace paths must be Uppercase first letter!
namespace Wowlib;

class Driver
{
    public $CDB;
    public $WDB;
    protected $config;
    
    // Out wowlib driver and emulator
    protected $driver;
    protected $emulator;
    protected $loaded = array();
    

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($emulator, $driver, $char, $world)
    {
        // Load the loader class
        $this->load = load_class('Loader');
        
        // First, we must load the driver config!
		$file = path( WOWLIB_ROOT, 'drivers', strtolower($emulator), strtolower($driver), 'Driver.php' );
        
        // If extension doesnt exist, return false
        if( !file_exists( $file ) ) throw new \Exception('Config file for driver '. $driver .' not found');
        require $file;
        
        // Load the config variables into a local variable
        $this->config = $config;
        $this->CDB = false;
        $this->WDB = false;
        
        // Load the character DB
        if(is_array($char))
        {
            try {
                $this->CDB = $this->load->database($char, false, true);
            }
            catch(\Exception $e) {
                $this->CDB = false;
            }
        }
        
        // Load world DB
        if(is_array($world))
        {
            try {
                $this->WDB = $this->load->database($world, false, true);
            }
            catch(\Exception $e) {
                $this->WDB = false;
            }
        }

        // Finally set our emulator and driver variables
        $this->emulator = $emulator;
        $this->driver = $driver;
    }
    
/*
| ---------------------------------------------------------------
| Method: classExists()
| ---------------------------------------------------------------
|
| This method returns if the namespaced driver class exists, and
| has been loaded.
|
| @Param: (String) The driver class name
| @Param: (Bool) $include - Include the class file if the class
|   hasnt been loaded yet?
| @Return: (Object)
|
*/
    public function classExists($name, $include = true)
    {
        $name = ucfirst( strtolower($name) );
        $driver = strtolower($this->driver);
        
        // Make sure if isnt loaded already
        if(in_array($name, $this->loaded)) return $this->loaded[$name];
        
        // Check for the extension
		$file = path( WOWLIB_ROOT, 'drivers', $this->emulator, $driver, $name .'.php' );
        
        // If extension doesnt exist, return false
        if( !file_exists( $file ) )
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
    
/*
| ---------------------------------------------------------------
| Method: getConfig()
| ---------------------------------------------------------------
|
| This method returns the config array
|
| @Return: (Array)
|
*/
    public function getConfig()
    {
        return $this->config;
    }
    
/*
| ---------------------------------------------------------------
| Method: getColumnById()
| ---------------------------------------------------------------
|
| This method returns the column name for the given ID's
|
| @Param: (String) $table - The table ID
| @Param: (String) $col - The column ID ID
| @Return: (String)
|
*/
    public function getColumnById($table, $col)
    {
        // Make sure the config key exists
        if(!isset($this->config["{$table}Columns"][$col])) return false;
        
        return $this->config["{$table}Columns"][$col];
    }
    
/*
| ---------------------------------------------------------------
| Method: getDriverNamespace()
| ---------------------------------------------------------------
|
| This method returns the namespace to all driver classes
|
| @Return: (String)
|
*/
    public function getDriverNamespace()
    {
        return "\\Wowlib\\". ucfirst($this->emulator) ."\\". ucfirst($this->driver);
    }
    
/*
| ---------------------------------------------------------------
| Method: getCDB()
| ---------------------------------------------------------------
|
| This method returns the database connection object to the 
| characters database
|
| @Return: (Object)
|
*/
    public function getCDB()
    {
        return $this->CDB;
    }
    
/*
| ---------------------------------------------------------------
| Method: getWDB()
| ---------------------------------------------------------------
|
| This method returns the database connection object to the 
| world database
|
| @Return: (Object)
|
*/
    public function getWDB()
    {
        return $this->WDB;
    }
    
/*
| ---------------------------------------------------------------
| Extenstion loader
| ---------------------------------------------------------------
|
*/
    public function __get($name)
    {
        // Just return the extension if it exists
        $name = strtolower($name);
        if(isset($this->{$name})) return $this->{$name};
        
        // Create our classname
        $class = ucfirst( $name );
        $driver = strtolower($this->driver);
        $ucEmu = ucfirst($this->emulator);
        
        // Check for the extension
		$file = path( WOWLIB_ROOT, 'library', $class .'.php' );
        
        // If extension doesnt exist, return false
        if( !file_exists( $file ) ) return false;
        
        // Load the extension file
        require_once( $file );
        
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