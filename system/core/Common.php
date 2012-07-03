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
        // namespace "\Core\...")
        if( empty($parts[0]) ) $parts = array_shift($parts);

        // Upercase the filename
        $last = count($parts) - 1;
        $parts[$last] = ucfirst($parts[$last]);

        // Build our filepath
        $class_path = path( $parts );

        // Lets make our file path from the root directory
        $file = path( SYSTEM_PATH, $class_path . ".php" );

        // If the file exists, then include it, and return
        if(!file_exists($file))
        {
            // Failed to load class all together.
            show_error('autoload_failed', array( addslashes($className) ), E_ERROR);
        }
		
        require_once $file;
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
            $className = $type. '\\'. $className;
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
        $file = implode(DS, $parts);

        // Build our file path
        $file = SYSTEM_PATH . DS . $file .'.php';

        // Include our file. If it doesnt exists, class is un-obtainable.
        require_once $file;

        //  Initiate the new class into a variable
        try {
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
| Function: php_error_handler()
| ---------------------------------------------------------------
|
| Php uses this error handle instead of the default one because
| php calls this method statically
|
*/
    function php_error_handler($errno, $errstr, $errfile, $errline)
    {
		// Return false if there is no error code
        if(!$errno) return false;
        
        // Trigger
        load_class('Debug')->trigger_error($errno, $errstr, $errfile, $errline, debug_backtrace());

        // Don't execute PHP internal error handler
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Function: shutdown()
| ---------------------------------------------------------------
|
| Method for catching fatal and parse errors
|
*/
    function shutdown()
    {
        // Get las error, and confg option
        $catch = load_class('Config')->get('catch_fatal_errors', 'Core');
        $error = error_get_last();
        
        // Write debug / system logs
        $Debug = load_class('Debug');
        $Debug->write_logs();
        
        // If we have an error, only track if it's fatal
        if(is_array($error) && $catch == 1)
        {
            if($error['type'] == E_ERROR || $error['type'] == E_PARSE)
            {
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
        
        //Load language
        $lang = load_class('Language');
        $language = load_class('Config')->get('core_language', 'Core');
        $lang->set_language( $language );
        $lang->load('core_errors');
        $message = $lang->get($err_message);
        
        //Allow custom messages
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
        load_class('Debug')->trigger_error($lvl, $message, $calling['file'], $calling['line'], $backtrace);
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
        load_class('Debug')->show_error(404);
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
    function log_message($type, $message)
    {		
        // Init and spit the error
        load_class('Debug')->log($type, $message);
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
        if(isset($GLOBALS['_instance']) && is_object($GLOBALS['_instance']))
        {
            return $GLOBALS['_instance'];
        }
        elseif(class_exists('Core\\Controller', FALSE))
        {
            return \Core\Controller::get_instance();
        }

        return FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Function: register_instance()
| ---------------------------------------------------------------
|
| This function sets the registered instance to the specified object
|
| @Return: (Object) - The instance object
| @Return: (None)
|
*/	
    function register_instance($Obj)
    {
        $GLOBALS['_instance'] = $Obj;
    }
	
/*
| ---------------------------------------------------------------
| Function: path()
| ---------------------------------------------------------------
|
| Combines several strings into a file path.
|
| @Params: (String | Array) - The pieces of the path, passed as 
|   individual arguments. Each argument can be an array of paths,
|   a string foldername, or a mixture of the two.
| @Return: (String) - The path, with the corrected Directory Seperator
|
*/

	function path()
	{
		$IsWindows = strtoupper( substr( PHP_OS, 0, 3 ) ) === "WIN";
		$args = func_get_args();
		$parts = array();
		
		foreach( $args as $part )
			$parts[] = (is_array( $part )) ? trim( implode(DS, $part) ) : trim($part);
			
		$newPath = implode( DS, $parts );
		
		if( $IsWindows ) //So some checking for illegal path chars
		{
			$IllegalChars = "/:?*\"<>|\r\n\\";
			$Pattern = "~[" . preg_quote( $IllegalChars ) . "]~";
				
			$tempPath = preg_replace( "~^[A-Z]{1}:~", "", $newPath );
			$tempPath = trim( $tempPath, DS );
			$tempPath = explode( DS, $tempPath );
			
			foreach( $tempPath as $part )
			{
				if( preg_match( $Pattern, $part ) )
				{
					show_error( "illegal_chars_in_path", array( $part ) );
					return null;
				}
			}
		}
		
		return $newPath;
	}

// Register the Core to process errors with the custom_error_handler method
set_error_handler('php_error_handler', E_ALL | E_STRICT);
register_shutdown_function('shutdown');
// EOF