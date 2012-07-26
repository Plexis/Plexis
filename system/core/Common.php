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
        if(strpos($className, $type) === FALSE) $className = $type .'\\'. $className;

        // Make a lowercase version, and a storage name
        $class = strtolower($className);
        $store_name = str_replace('\\', '_', $class);

        // Check the registry for the class, If its there, then return the class
        $loaded = \Registry::load($store_name);
        if($loaded !== NULL) return $loaded;

        // ---------------------------------------------------------
        // Class not in Registry, So we load it manually and then  |
        // store it in the registry for future static use          |
        // ---------------------------------------------------------

        // We need to find the file the class is stored in. Good thing the
        // Namespaces are pretty much paths to the class ;)
        $parts = explode('\\', $class);
        $last = count($parts) - 1;
        $parts[$last] = ucfirst($parts[$last]);

        // Build our filepath
        $file = implode(DS, $parts);
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
        if(!is_object($Obj) && !$surpress)
        {
            show_error('class_init_failed', array($className, $message), E_ERROR);
        }

        // Store this new object in the registery
        \Registry::store($store_name, $Obj); 

        // return the object.
        return $Obj;
    }

/*
| ---------------------------------------------------------------
| Function: show_error()
| ---------------------------------------------------------------
|
| This function is used to simplify the showing of errors
|
| @Param: (String) $err_message - Error message code
| @Param: (Array) $args - An array for vsprintf to replace in the message.
| @Param: (Int) $lvl - Level of the error
| @Param: (Bool) $fatal - If set to true, error cannot be silenced
| @Return: (None)
|
*/
    function show_error($err_message = 'none', $args = null, $lvl = E_ERROR, $fatal = false)
    {
        // Let get a backtrace for deep debugging
        $backtrace = debug_backtrace();
        $calling = $backtrace[0];
        
        // Load language
        $lang = load_class('Language');
        $lang->load('core_errors');
        $message = $lang->get($err_message);
        
        // Allow custom messages
        if($message === FALSE) $message = $err_message;
        
        // Do replacing
        if(is_array($args)) $message = vsprintf($message, $args);
        
        // Let the debugger take over from here
        if($fatal) \Debug::silent_mode(false);
        \Debug::trigger_error($lvl, $message, $calling['file'], $calling['line']);
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
        \Debug::show_error(404);
    }
    
/*
| ---------------------------------------------------------------
| Function: log_message()
| ---------------------------------------------------------------
|
| Logs a message in the system log
|
| @Param: (String) $type - The message type (info, error)
| @Param: (String) $message - The message
| @Return: (None)
|
*/
    function log_message($type, $message)
    {
        \Debug::log($type, $message);
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
        // Determine if we are one windows, And get our path parts
        $IsWindows = strtoupper( substr(PHP_OS, 0, 3) ) === "WIN";
        $args = func_get_args();
        $parts = array();
        
        // Trim our paths to remvove spaces and new lines
        foreach( $args as $part )
        {
            $parts[] = (is_array( $part )) ? trim( implode(DS, $part) ) : trim($part);
        }

        // Get our cleaned path into a variable with the correct directory seperator
        $newPath = implode( DS, $parts );
        
        // Do some checking for illegal path chars
        if( $IsWindows )
        {
            $IllegalChars = "\\/:?*\"<>|\r\n";
            $Pattern = "~[" . $IllegalChars . "]+~";
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
// EOF