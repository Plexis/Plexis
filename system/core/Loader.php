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
| Class: Loader()
| ---------------------------------------------------------------
|
| This class is used to load classes and librarys into the calling
| class / method.
|
*/
namespace System\Core;

class Loader
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
        require_once(APP_PATH . DS . 'models' . DS . $name .'.php');
        
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
| @Param: (String) $_view_name - The name of the controllers view file
|   can also be path/to/view/$_view_name
| @Param: (Array) $_data - an array of variables to be extracted
| @Param: (Bool) $_return_view - Return the page instead of echo it?
|
*/
    public function view($_view_name, $_data, $_return_view = FALSE)
    {
        // Make sure our data is in an array format
        if(!is_array($_data))
        {
            show_error('non_array', array('data', 'Loader::view'), E_WARNING);
            $_data = array();
        }
        
        // To prevent overwriting a variable, we give it a funky name
        $_file_path = APP_PATH . DS . 'views' . DS . $_view_name . '.php';

        // extract variables
        extract($_data);	 

        // Get our page contents
        if(file_exists( $_file_path ))
        {
            ob_start();
            include( $_file_path );
            $page = ob_get_contents();
            ob_end_clean();
        
            // Replace some Global values
            $Benchmark = load_class('Benchmark');
            $page = str_replace('{ELAPSED_TIME}', $Benchmark->elapsed_time('system', 4), $page);
            $page = str_replace('{MEMORY_USAGE}', $Benchmark->memory_usage(), $page);

            // Spit out the page
            if($_return_view == FALSE) echo $page;
            return $page;
        }
        else
        {
            show_error('missing_page_view', array($_view_name), E_ERROR);
            return FALSE;
        }
    }

/*
| ---------------------------------------------------------------
| Method: library()
| ---------------------------------------------------------------
|
| This method is used to call in a class from either the APP
| library, or the system library folders.
|
| @Param: (String) $name - The name of the class, with or without namespacing
| @Param: (Mixed) $instance - Do we instance the class? May also specify
|   the instance name (IE: class Test instance as TeStInG)
| @Param: (Bool) $surpress - set to TRUE to bypass the error screen
|   if the class fails to initiate, and return false instead
| @Return: (Object) Returns the library class
|
*/
    public function library($name, $instance = TRUE, $surpress = FALSE)
    {
        // Make sure periods are replaced with slahes if there is any
        if(strpos($name, ".") !== FALSE) $name = str_replace('.', '\\', $name);
        
        // Load the Class
        $class = load_class($name, 'Library', $surpress);
        
        // Do we instance this class?
        if($instance !== FALSE)
        {
            // Allow for custom class naming
            ($instance !== TRUE) ? $name = $instance : '';
            
            // Instance
            $FB = get_instance();
            if($FB !== FALSE)
            {
                (!isset($FB->$name)) ? $FB->$name = $class : '';
            }
        }
        return $class;
    }

/*
| ---------------------------------------------------------------
| Method: database()
| ---------------------------------------------------------------
|
| This method is used to setup a database connection
|
| @Param: (String) $args - The indentifier of the DB connection in 
|   the DB config file.
| @Param: (Mixed) $instance - If you want to instance the connection
|   in the controller, set to TRUE, or the instance variable desired
| @Param: (Bool) $surpress - set to TRUE to bypass the error screen
|   if the connection failes, and just return false
| @Return: (Object) Returns the database object / connection
|
*/
    public function database($args, $instance = TRUE, $surpress = FALSE)
    {
        // Load our connection settings. We can allow custom connection arguments
        if(!is_array($args))
        {
            // Check our registry to see if we already loaded this connection
            $Obj = \Registry::singleton()->load("DBC_".$args);
            if($Obj !== NULL)
            {
                // Skip to the instancing part unless we set instance to FALSE
                if($instance != FALSE) goto Instance;
                return $Obj;
            }
        
            // Get the DB connection information
            $info = config($args, 'DB');
            if($info === NULL)
            {
                show_error('db_key_not_found', array($args), E_ERROR);
            }
        }
        else
        {
            // Assign our $info variable, and set our connection name to $instance (unless it equals true or 1)
            $info = $args;
            if(is_bool($instance) || is_numeric($instance))
            {
                $instance = FALSE;
                $args = 'custom_database';
            }
            else
            {
                $args = $instance;
            }
        }
        
        // Check for a DB class in the Application, and system core folder
        $info['driver'] = strtolower($info['driver']);
        if(file_exists(APP_PATH. DS . 'database' . DS . 'Driver.php')) 
        {
            require_once(APP_PATH. DS . 'database' . DS . 'Driver.php');
            $first = "Application\\";
        }
        else
        {
            require_once(SYSTEM_PATH. DS . 'database' . DS . 'Driver.php');
            $first = "System\\";
        }
        
        // Not in the registry, so istablish a new connection
        $dispatch = $first ."Database\\Driver";
        try{
            $Obj = new $dispatch( $info );
        }
        catch(\Exception $e) {
            $Obj = FALSE;
        }
        
        // Error?
        if($surpress == FALSE && $Obj == FALSE)
        {
            show_error('db_connect_error', array( $info['database'], $info['host'], $info['port'] ), E_ERROR);
        }
        
        // Store the connection in the registry
        \Registry::singleton()->store("DBC_".$args, $Obj);		
        
        // Here is our instance goto
        Instance:
        {
            // If user wants to instance this, then we do that
            if($instance != FALSE && !is_numeric($args))
            {
                if($instance === TRUE) $instance = $args;

                // Easy way to instance the connection is like this
                $FB = get_instance();
                if($FB !== FALSE)
                {
                    (!isset($FB->$instance)) ? $FB->$instance = $Obj : '';
                }
            }
        }
        
        // Return the object!
        return $Obj;
    }

/*
| ---------------------------------------------------------------
| Method: helper()
| ---------------------------------------------------------------
|
| This method is used to call in a helper file from either the 
| application/helpers, or the core/helpers folders.
|
| @Param: (String) $name - The name of the helper file
| @Return: (None)
|
*/
    public function helper($name)
    {
        // Check the application/helpers folder
        if(file_exists(APP_PATH . DS .  'helpers' . DS . $name . '.php')) 
        {
            require_once(APP_PATH . DS .  'helpers' . DS . $name . '.php');
        }
        
        // Check the core/helpers folder
        else 
        {
            require_once(SYSTEM_PATH . DS .  'helpers' . DS . $name . '.php');
        }
    }
}
// EOF