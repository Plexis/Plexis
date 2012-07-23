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

// All namespace paths must be Uppercase first letter!
namespace Wowlib;

// Kill the script if its a direct link!
if( !defined('CMS_VERSION') ) die('Unauthorized');

class Wowlib
{
    // Our DB Connections
    public $RDB;
    public $CDB;
    public $WDB;
    
    // Out wowlib driver and emulator
    protected $driver;
    protected $emulator;
    
    // Have we loaded interfaces?
    protected static $loaded = false;
    

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($driver, $char, $world)
    {
        // Load the Loader class
        $this->load = load_class('Loader');
        
        // Set the connections into the connection variables
        $this->RDB = $this->load->database('RDB', false, true);
        $this->CDB = $this->load->database($char, false, true);
        $this->WDB = $this->load->database($world, false, true);

        // Finally set our emulator and driver variables
        $this->emulator = config('emulator');
        $this->driver = $driver;
        
        // Autoload all interfaces just once ;)
        if(!self::$loaded)
        {
            $path = path( ROOT, 'third_party', 'wowlib', 'interfaces' );
            $list = $this->load->library('Filesystem')->list_files($path);
            foreach($list as $interface)
            {
                include_once($path . DS . $interface);
            }
            self::$loaded = true;
        }
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
        
        // Check for the extension
		$file = path( ROOT, 'third_party', 'wowlib', 'emulators', $this->emulator, $driver, $class .'.php' );
        if( !file_exists( $file ) )
        {
            // Extension doesnt exists :O
            show_error('Failed to load wowlib extentsion %s', array($name), E_ERROR);
            return false;
        }
        
        // Load the extension file
        require_once( $file );
        
        // Load the class
        $class = "\\Wowlib\\{$this->driver}\\". $class;
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