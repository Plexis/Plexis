<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Loader()
| ---------------------------------------------------------------
|
| This class is used to load classes and librarys into the calling
| class / method.
|
*/
namespace Application\Core;

class Loader extends \System\Core\Loader
{

/*
| ---------------------------------------------------------------
| Method: model()
| ---------------------------------------------------------------
|
| This method is used to call in a model
|
| @Param: (String) $name - The name of the model. You may also go path/to/$name
| @Param: (Mixed) $instance_as - How you want to access it as in the 
|   controller (IE: $instance_as = test; In controller: $this->test)
| @Return: (Object) Returns the model
|
*/
    public function model($name, $instance_as = NULL)
    {
        // Check for path. We need to get the model file name
        if(strpos($name, '/') !== FALSE)
        {
            $paths = explode('/', $name);
            $class = ucfirst( end($paths) );
        }
        else
        {
            $class = ucfirst($name);
            $name = strtolower($name);
        }
        
        // Include the model page
        if($GLOBALS['is_module'] == TRUE)
        {
            require(APP_PATH . DS .'modules'. DS . $GLOBALS['controller'] . DS .'models'. DS . $name .'.php');
        }
        else
        {
            require(APP_PATH . DS . 'models' . DS . $name .'.php');
        }
        
        // Get our class into a variable
        $Obj = new $class();

        // Instnace the Model in the controller
        if($instance_as !== NULL)
        {
            get_instance()->$instance_as = $Obj;
        }
        else
        {
            get_instance()->$class = $Obj;
        }
        return $Obj;
    }

/*
| ---------------------------------------------------------------
| Method: view()
| ---------------------------------------------------------------
|
| This method is used to load the view file and display it
|
| @Param: (String) $name - The name of the requested view file
| @Param: (Array) $data - an array of variables to be extracted
| @Param: (Bool) $skip - Skip the template system and use parent?
|
*/
    public function view($name, $data = array(), $skip = FALSE)
    {
        // If we are requesting to use the default render system
        if($skip == TRUE)
        {
            parent::view($name, $data);
        }
        else
        {
            // We are just going to let the template engine handle this
            $template = $this->library('Template');
            $template->render($name, $data);
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: plugin()
| ---------------------------------------------------------------
|
| This method is used to load a plugin
|
| @Param: (String) $name - The name of the plugin
| @Param: (String) $method - The method to run
|
*/
    public function plugin($name, $method = false)
    {
        // Create our classname
        $name = ucfirst($name);
        $store_name = 'Plugins_'. $name;
        
        // Check if the plugin is already loaded
        $Obj = \Registry::singleton()->load($store_name);
        if( $Obj === null )
        {
            // We have to manually load the plugin
            $file = APP_PATH . DS . 'plugins' . DS . $name . '.php';
            if(!file_exists($file))
            {
                show_error('plugin_not_found', array($name), E_ERROR);
                return false;
            }
            
            // Include the file just once!
            include_once( $file );
            
            // Init the plugin
            try {
                $className = "\Plugins\\". $name;
                $Obj = new $className();
            } 
            catch(\Exception $e) {
                $Obj = false;
                show_error('plugin_failed_init', array($name, $e->getMessage()), E_WARNING);
            }
            
            // Store the object
            \Registry::singleton()->store($store_name, $Obj);
        }
        
        // Make sure the object IS an object
        if( !is_object($Obj) ) return false;
        
        // Run the requested method
        if($method != false)
        {
            try {
                $Obj->$method();
                $return = true;
            } 
            catch(\Exception $e) {
                $return = false;
                show_error('plugin_error', array($name, $method, $e->getMessage()), E_WARNING);
            }
            
            // Return the result
            return $return;
        }
        
        return $Obj;
    }

/*
| ---------------------------------------------------------------
| Method: wowlib()
| ---------------------------------------------------------------
|
| This method is used to load a WoW library
|
| @Param: (Int) $id - The realm ID as stored in the `scms_realms` table
| @Param: (String) $instance_as - The name the instance variable
|   Ex: $this->$instance_as->method()
|
*/
    public function wowlib($id = 0, $instance_as = FALSE)
    {
        // Get our realm id if none is provieded
        if($id === 0) $id = config('default_realm_id');
        
        // Make sure we havent loaded the lib already
        $Obj = \Registry::singleton()->load('Wowlib_r'.$id);
        if($Obj !== NULL) return $Obj;
        
        // Load our driver name
        $DB = $this->database('DB', FALSE);
        $realm = $DB->query("SELECT `id`, `name`, `driver`, `char_db`, `world_db` FROM `pcms_realms` WHERE `id`=".$id)->fetch_row();
        
        // Make sure we didnt get a false DB return
        if($realm === FALSE)
        {
            $language = load_language_file('messages');
            $message = $language['wowlib_realm_doesnt_exist'];
            show_error($message, array($id), E_ERROR);
        }
        
        // Make sure the wowlib exists
        if( !is_dir( APP_PATH . DS . 'wowlib' . DS . config('emulator') . DS . $realm['driver']) )
        {
            $language = load_language_file('messages');
            $message = $language['wowlib_driver_doesnt_exist'];
            show_error($message, array($realm['driver']), E_ERROR);
            return false;
        }

        // Include the wowlib file
        require_once( APP_PATH . DS . 'wowlib' . DS . 'Wowlib.php' );
        
        // Try to init the class
        try{
            $class = new \Wowlib\Wowlib($realm);
        }
        catch(\Exception $e){
            $class = FALSE;
        }
        
        // Store the class statically and return the class
        \Registry::singleton()->store('Wowlib_r'.$id, $class);
        
        // Check to see if the user wants to instance
        if($instance_as !== FALSE)
        {
            get_instance()->$instance_as = $class;
        }
        return $class;
    }
    
/*
| ---------------------------------------------------------------
| Method: realm()
| ---------------------------------------------------------------
|
| This method is used to load a WoW Emulator, and connect to 
|   the realm
|
|   @Param: $instance - Instance the realm?
|
*/
    public function realm($instance = TRUE)
    {  
        // Get our emulator from the Config File
        $emulator = ucfirst( config('emulator') );
        $class_name = "Emulator_".$emulator;
        $file = APP_PATH . DS . 'wowlib' . DS . strtolower($emulator) . DS . $emulator . '.php';
        
        // Make sure we havent loaded the lib already
        $class = \Registry::singleton()->load($class_name);
        if($class !== NULL)
        {
            goto Instance;
        }

        // Load the lib file
        elseif(file_exists($file))
        {
            include_once $file;
            $name = "\\Wowlib\\". $emulator;
            $class = new $name();
            
            // Store the class statically and return the class
            \Registry::singleton()->store($class_name, $class);
            
            // Instance
            Instance:
            {
                if($instance == TRUE)
                {
                    $FB = get_instance();
                    ($FB !== FALSE) ? $FB->realm = $class : '';
                }
            }
            
            // Return the class
            return $class;
        }
        else
        {
            $language = load_language_file('messages');
            $message = $language['emulator_doesnt_exist'];
            show_error($message, array($emulator), E_ERROR);
        }
    }
}
// EOF