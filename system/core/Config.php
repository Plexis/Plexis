<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
| ---------------------------------------------------------------
| Class: Config()
| ---------------------------------------------------------------
|
| Main Config class. used to load, set, and save variables used
| in the config file.
|
*/
namespace System\Core;

class Config
{
    // An array of all out stored containers / variables
    protected $data = array();

    // A list of our loaded config files
    protected $files = array();

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
*/
    public function __construct() 
    {
        // Set default files
        $this->files['app']['file_path'] = APP_PATH . DS . 'config' . DS . 'config.php';
        $this->files['core']['file_path'] = APP_PATH . DS . 'config' . DS . 'core.config.php';
        $this->files['db']['file_path'] = APP_PATH . DS . 'config' . DS . 'database.config.php';
        
        // Lets roll!
        $this->Init();
    }

/*
| ---------------------------------------------------------------
| Method: Init()
| ---------------------------------------------------------------
|
| Initiates the default config files (App, Core, and DB)
|
| @Return: (None)
|
*/
    protected function Init() 
    {
        // Load the APP config.php and add the defined vars
        $this->load($this->files['app']['file_path'], 'app');
        
        // Load the core.config.php and add the defined vars
        $this->load($this->files['core']['file_path'], 'core', 'config');
        
        // Load the database.config.php and add the defined vars
        $this->load($this->files['db']['file_path'], 'db', 'DB_configs');
    }

/*
| ---------------------------------------------------------------
| Method: get()
| ---------------------------------------------------------------
|
| Returns the variable ($key) value in the config file.
|
| @Param: (String) $key - variable name. Value is returned
| @Param: (Mixed) $type - config variable container name
| @Return: (Mixed) May return NULL if the var is not set
|
*/
    public function get($key, $type = 'App') 
    {
        // Lowercase the type
        $type = strtolower($type);
        
        // Check if the variable exists
        if(isset($this->data[$type][$key])) 
        {
            return $this->data[$type][$key];
        }
        return NULL;
    }
    
/*
| ---------------------------------------------------------------
| Method: get()
| ---------------------------------------------------------------
|
| Returns all variables in an array from the the config file.
|
| @Param: (Mixed) $type - config variable container name
| @Return: (Array) May return NULL if the var is not set
|
*/
    public function get_all($type = 'App') 
    {
        // Lowercase the type
        $type = strtolower($type);
        
        // Check if the variable exists
        if(isset($this->data[$type]))
        {
            return $this->data[$type];
        }
        return NULL;
    }

/*
| ---------------------------------------------------------------
| Method: set()
| ---------------------------------------------------------------
|
| Sets the variable ($key) value. If not saved, default value
| will be returned as soon as page is re-loaded / changed.
|
| @Param: (String or Array) $key - variable name to be set
| @Param: (Mixed) $value - new value of the variable
| @Param: (Mixed) $name - The container name for the $key variable
| @Return: (None)
|
*/
    public function set($key, $val = false, $name = 'App') 
    {
        // Lowercase the $name
        $name = strtolower($name);
        
        // If we have array, loop through and set each
        if(is_array($key))
        {
            foreach($key as $k => $v)
            {
                $this->data[$name][$k] = $v;
            }
        }
        else
        {
            $this->data[$name][$key] = $val;
        }
    }

/*
| ---------------------------------------------------------------
| Method: Load()
| ---------------------------------------------------------------
|
| Load a config file, and adds its defined variables to the $data
|   array
|
| @Param: (String) $_file - Full path to the config file, includeing name
| @Param: (String) $_name - The container name we are storing this configs
|   variables to.
| @Param: (String) $_array - If the config vars are stored in an array, whats
|   the array variable name?
| @Return: (None)
|
*/
    public function load($_file, $_name, $_array = FALSE) 
    {
        // Lowercase the $name
        $_name = strtolower($_name);
        
        // Include file and add it to the $files array
        if(!file_exists($_file)) return FALSE;
        include( $_file );
        $this->files[$_name]['file_path'] = $_file;
        $this->files[$_name]['config_key'] = $_array;
        
        // Get defined variables
        $vars = get_defined_vars();
        if($_array != FALSE) $vars = $vars[$_array];
        
        // Unset the passes vars
        unset($vars['_file'], $vars['_name'], $vars['_array']);
        
        // Add the variables to the $data[$name] array
        if(count($vars) > 0)
        {
            foreach( $vars as $key => $val ) 
            {
                if($key != 'this' && $key != 'data') 
                {
                    $this->data[$_name][$key] = $val;
                }
            }
        }
        return;
    }

/*
| ---------------------------------------------------------------
| Method: Save()
| ---------------------------------------------------------------
|
| Saves all set config variables to the config file, and makes 
| a backup of the current config file
|
| @Param: (String) $name - Name of the container holding the variables
| @Return: (Bool) TRUE on success, FALSE otherwise
|
*/
    public function save($name) 
    {
        // Lowercase the $name
        $name = strtolower($name);
        
        // Check to see if we need to put this in an array
        $ckey = $this->files[$name]['config_key'];
        if($ckey != FALSE)
        {
            $Old_Data = $this->data[$name];
            $this->data[$name] = array("$ckey" => $this->data[$name]);
        }

        // Create our new file content
        $cfg  = "<?php\n";

        // Loop through each var and write it
        foreach( $this->data[$name] as $key => $val )
        {
            if(is_numeric($val)) 
            {
                $cfg .= "\$$key = " . $val . ";\n";
            } 
            elseif(is_array($val))
            {
                $val = var_export($val, TRUE);
                $cfg .= "\$$key = " . $val . ";\n";
            }
            else
            {
                $cfg .= "\$$key = '" . addslashes( $val ) . "';\n";
            }
        }

        // Close the php tag
        $cfg .= "?>";
        
        // Add the back to non array if we did put it in one
        if($ckey != FALSE)
        {
            $this->data[$name] = $Old_Data;
        }
        
        // Copy the current config file for backup, 
        // and write the new config values to the new config
        copy($this->files[$name]['file_path'], $this->files[$name]['file_path'].'.bak');
        if(file_put_contents( $this->files[$name]['file_path'], $cfg )) 
        {
            return TRUE;
        } 
        else 
        {
            return FALSE;
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: Restore()
| ---------------------------------------------------------------
|
| This method is used to undo the last Save. .bak file must be
|   in the config folder
|
| @Param: (String) $name - Name of the container holding the variables
| @Return: (Bool) TRUE on success, FALSE otherwise
|
*/
    public function restore($name) 
    {
        // Copy the backup config file nd write the config values to the current config
        return copy($this->files[$name]['file_path'].'bak', $this->files[$name]['file_path']);
    }
}
// EOF