<?php
/* 
| --------------------------------------------------------------
| 
| Plexis CMS
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
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
    function model($name, $instance_as = NULL)
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
| @Param: (String) $name - The name of the controllers view file
| @Param: (Array) $data - an array of variables to be extracted
|
*/
    function view($name, $data = array(), $return = FALSE)
    {
        // We are just going to let the template engine handle this
        $template = $this->library('Template');
        $template->render($name, $data);
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
        if($id === 0)
        {
            // Load the session and get the users selected realm id
            $session = $this->library('Session')->get('user');
            $id = (isset($session['realm_id'])) ? $session['realm_id'] : config('default_realm_id');
        }
        
        // Load our driver name
        $DB = $this->database('DB', FALSE);
        $name = $DB->query("SELECT `driver` FROM `pcms_realms` WHERE `id`=".$id)->fetch_column();
        
        // Make sure we didnt get a false DB return
        if($name === FALSE)
        {
            $language = load_language_file('messages');
            $message = $language['wowlib_realm_doesnt_exist'];
            show_error($message, array($id), E_ERROR);
        }
        
        // Define our classname
        $reference_name = strtolower($name);
        $class_name = ucfirst($reference_name);
        $file = APP_PATH . DS . 'library' . DS . 'wowlib' . DS . $class_name . '.php';
        
        // Make sure we havent loaded the lib already
        $Obj = \Registry::singleton()->load($class_name);
        if($Obj !== NULL)
        {
            return $Obj;
        }

        // Load the lib file
        elseif(file_exists($file))
        {
            include_once $file;
            $name = "\\Application\\Library\\Wowlib\\". $class_name;
            $class = new $name();
            
            // Store the class statically and return the class
            \Registry::singleton()->store($class_name, $class);
            
            // Check to see if the user wants to instance
            if($instance_as !== FALSE)
            {
                get_instance()->$instance_as = $class;
            }
            return $class;
        }
        else
        {
            $language = load_language_file('messages');
            $message = $language['wowlib_driver_doesnt_exist'];
            show_error($message, array($class_name), E_ERROR);
        }
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
        $file = APP_PATH . DS . 'library' . DS . 'emulators' . DS . $emulator . '.php';
        
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
            $name = "\\Application\\Library\\Emulators\\". $emulator;
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