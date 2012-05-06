<?php
/*
| ---------------------------------------------------------------
| Method: __autoload()
| ---------------------------------------------------------------
|
| This function is used to autoload files of delcared classes
| that have not been included yet
|
| @Param: (String) $className - Class name to autoload ofc silly
| @Return: (None)
|
*/
    function __autoload($className) 
    {	
        // We will need to lowercase everything except for the filename
        $parts = explode('\\', strtolower($className));

        // Shave the first value if empty (it happens when going from the root 
        // namespace "\\System\\Core...")
        if( empty($parts[0]) ) $parts = array_shift($parts);

        // Upercase the filename
        $last = count($parts) - 1;
        $parts[$last] = ucfirst($parts[$last]);

        // Build our filepath
        $class_path = implode(DS, $parts);

        // Lets make our file path from the root directory
        $file = ROOT . DS . $class_path .'.php';

        // If the file exists, then include it, and return
        if(!file_exists($file))
        {
            // Failed to load class all together.
            show_error('autoload_failed', array( addslashes($className) ), E_ERROR);
        }
        require_once($file);
    }

/*
| ---------------------------------------------------------------
| Function: php_error_handler()
| ---------------------------------------------------------------
|
| Php uses this error handle instead of the default one because
| php calls this method statically
|
*/
    function php_error_handler($errno, $errstr, $errfile, $errline)
    {
        if(!$errno) 
        {
            // This error code is not included in error_reporting
            return;
        }
        
        // Get singleton
        $Debug = load_class('Debug');	
        
        // Trigger
        $Debug->trigger_error($errno, $errstr, $errfile, $errline, debug_backtrace());

        // Don't execute PHP internal error handler
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Function: shutdown()
| ---------------------------------------------------------------
|
| Method for catching fetal and parse errors
|
*/
    function shutdown()
    {
        $error = error_get_last();
        if(is_array($error) && config('catch_fetal_errors', 'Core') == 1)
        {
            if($error['type'] == E_ERROR || $error['type'] == E_PARSE)
            {
                // Get singleton
                $Debug = load_class('Debug');	
            
                // Trigger
                $Debug->trigger_error($error['type'], $error['message'], $error['file'], $error['line']);
            }
            // Otherwise ignore
        }
    }

/*
| ---------------------------------------------------------------
| Function: show_error()
| ---------------------------------------------------------------
|
| This function is used to simplify the showing of errors
|
| @Param: (String) $err_message - Error message code
| @Param: (Array) $args - An array for vsprintf to replace in the 
| @Param: (Int) $lvl - Level of the error
| @Return: (None)
|
*/	
    function show_error($err_message = 'none', $args = NULL, $lvl = E_ERROR)
    {
        // Let get a backtrace for deep debugging
        $backtrace = debug_backtrace();
        $calling = $backtrace[0];
        
        // Load language
        $lang = load_class('Language');
        $lang->set_language( config('core_language', 'Core') );
        $lang->load('core_errors');
        $message = $lang->get($err_message);
        
        // Allow custom messages
        if($message === FALSE)
        {
            $message = $err_message;
        }
        
        // do replacing
        if(is_array($args))
        {
            $message = vsprintf($message, $args);
        }
        
        // Init and spit the error
        $Debug = load_class('Debug');
        $Debug->trigger_error($lvl, $message, $calling['file'], $calling['line'], $backtrace);
    }
	
/*
| ---------------------------------------------------------------
| Function: show_404()
| ---------------------------------------------------------------
|
| Displays the 404 Page
|
| @Return: (None)
|
*/	
    function show_404()
    {		
        // Init and spit the error
        $Debug = load_class('Debug');
        $Debug->show_error(404);
    }
    
/*
| ---------------------------------------------------------------
| Function: log_message()
| ---------------------------------------------------------------
|
| Logs a message in the debug log, or specified file
|
| @Return: (None)
|
*/	
    function log_message($message, $filename = 'debug.log')
    {		
        // Init and spit the error
        $Debug = load_class('Debug');
        $Debug->log($message, $filename);
    }

/*
| ---------------------------------------------------------------
| Method: config()
| ---------------------------------------------------------------
|
| This function is used to return a config value from a config
| file.
|
| @Param: (String) $item - The config item we are looking for
| @Param: (Mixed) $type - Name of the config variables, this is set 
|	when you load the config, defaults are Core, App and Mod
| @Return: (Mixed) - Returns the config vaule of $item
|
*/
    function config($item, $type = 'App')
    {
        $Config = load_class('Config');		
        return $Config->get($item, $type);
    }

/*
| ---------------------------------------------------------------
| Method: config_set()
| ---------------------------------------------------------------
|
| This function is used to set site config values. This does not
| set core, or database values.
|
| @Param: (String) $item - The config item we are setting a value for
| @Param: (Mixed) $value - the value of $item
| @Param: (Mixed) $name - The name of this config variables container
| @Return: (None)
|
*/
    function config_set($item, $value, $name = 'App')
    {
        $Config = load_class('Config');	
        $Config->set($item, $value, $name);
    }

/*
| ---------------------------------------------------------------
| Method: config_save()
| ---------------------------------------------------------------
|
| This function is used to save site config values to the condig.php. 
| *Warning - This will remove any and ALL comments in the config file
|
| @Param: (Mixed) $name - Which config are we saving? App? Core? Module?
| @Return: (None)
|
*/
    function config_save($name)
    {
        $Config = load_class('Config');	
        return $Config->save($name);
    }

/*
| ---------------------------------------------------------------
| Method: load_config()
| ---------------------------------------------------------------
|
| This function is used to get all defined variables from a config
| file.
|
| @Param: (String) $file - full path and filename to the config file being loaded
| @Param: (Mixed) $name - The name of this config variables, for later access. Ex:
| 	if $name = 'test', the to load a $var -> config( 'var', 'test');
| @Param: (String) $array - If the config vars are stored in an array, whats
|	the array variable name?
| @Return: (None)
|
*/
    function load_config($file, $name, $array = FALSE)
    {	
        $Config = load_class('Config');	
        $Config->load($file, $name, $array);
    }

/*
| ---------------------------------------------------------------
| Function: get_instance()
| ---------------------------------------------------------------
|
| Gateway to adding the controller into your current working class
|
| @Return: (Object) - Return the instnace of the Controller
|
*/	
    function get_instance()
    {
        if(class_exists('Application\\Core\\Controller', FALSE))
        {
            return Application\Core\Controller::get_instance();
        }
        elseif(class_exists('System\\Core\\Controller', FALSE))
        {
            return System\Core\Controller::get_instance();
        }
        else
        {
            return FALSE;
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: get_url_info()
| ---------------------------------------------------------------
|
| Simple way of getting the site url and url information
|
| @Return: (Object) - Return the instnace of the Controller
|
*/	
    function get_url_info()
    {
        return load_class('Router')->get_url_info();
    }

/*
| ---------------------------------------------------------------
| Function: load_class()
| ---------------------------------------------------------------
|
| This function is used to load and store core classes statically 
| that need to be loaded for use, but not reset next time the class
| is called.
|
| @Param: (String) $className - Class needed to be loaded / returned
| @Param: (String) $type - Basically the folder name where the class
|   is stored
| @Param: (Bool) $surpress - set to TRUE to bypass the error screen
|   if the class fails to initiate, and return false instead
| @Return: (Object) - Returns the loaded class
|
*/
    function load_class($className, $type = 'Core', $surpress = FALSE)
    {
        // Now we need to make sure the user supplied some sort of path
        if(strpos($className, '\\') === FALSE)
        {
            $className = $type .'\\'. $className;
        }

        // Make a lowercase version, and a storage name
        $class = strtolower($className);
        $store_name = str_replace('\\', '_', $class);

        // Check the registry for the class, If its there, then return the class
        $loaded = \Registry::singleton()->load($store_name);
        if($loaded !== NULL) return $loaded;

        // ---------------------------------------------------------
        // Class not in Registry, So we load it manually and then  |
        // store it in the registry for future static use          |
        // ---------------------------------------------------------

        // We need to find the file the class is stored in. Good thing the
        // Namespaces are pretty much paths to the class ;)
        $parts = explode('\\', $class);

        // Uppercase the filename
        $last = count($parts) - 1;
        $parts[$last] = ucfirst($parts[$last]);

        // Build our filepath
        $file = str_replace('\\', DS, implode('\\', $parts));

        // If we dont have the full path, create it
        if($parts[0] !== 'system' && $parts[0] !== 'application')
        {
            // Check for needed classes from the Application library folder
            if(file_exists(APP_PATH. DS . $file . '.php')) 
            {
                $file = APP_PATH . DS . $file .'.php';
                $className = '\Application\\'. $className;
            }
            else 
            {
                $file = SYSTEM_PATH . DS . $file .'.php';
                $className = '\System\\'. $className;
            }
        }
        else
        {
            $file = ROOT . DS . $file .'.php';
        }

        // Include our file. If it doesnt exists, class is un-obtainable.
        require($file);

        //  Initiate the new class into a variable
        try{
            $Obj = new $className();
        }
        catch(\Exception $e) {
            $message = $e->getMessage();
            $Obj = FALSE;
        }
        
        // Display error?
        if($Obj == FALSE && $surpress == FALSE)
        {
            show_error('class_init_failed', array($className, $message), E_ERROR);
        }

        // Store this new object in the registery
        \Registry::singleton()->store($store_name, $Obj); 

        // return the object.
        return $Obj;
    }

/*
| ---------------------------------------------------------------
| Method: redirect()
| ---------------------------------------------------------------
|
| This function is used to easily redirect and refresh pages
|
| @Param: (String) $url - Where were going
| @Param: (Int) $wait - How many sec's we wait till the redirect.
| @Return: (None)
|
*/
    function redirect($url, $wait = 0)
    {
        // Check for a valid URL. If not then add our current SITE_URL to it.
        if(!preg_match('@^(mailto|ftp|http(s)?)://@i', $url))
        {
            $url = SITE_URL .'/'. $url;
        }

        // Check for refresh or straight redirect
        if($wait >= 1)
        {
            header("Refresh:". $wait .";url=". $url);
        }
        else
        {
            header("Location: ".$url);
            die();
        }
    }

// Register the Core to process errors with the custom_error_handler method
set_error_handler('php_error_handler', E_ALL | E_STRICT);
register_shutdown_function('shutdown');
// EOF