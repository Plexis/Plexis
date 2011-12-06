<?php
/*
| ---------------------------------------------------------------
| Function: output_message
| ---------------------------------------------------------------
|
| This method produces messages to be displayed in the template.
| These messages include success, warning, and error messages.
|
| @Param: (String) $level - The level of the message. There are 
|	4 levels, 'error', 'warning', 'info', and 'success'.
| @Param: (String) $message - The message in the box
| @Param: (Array) $args - An array for replacing %s' (sprints)
| @Param: (String) $file - The message file to be loaded
| @Return (None)
|
*/
    function output_message($level, $message, $args = NULL, $file = 'messages')
    {
        // Load language
        $lang = load_language_file( $file );
        $text = (isset($lang[$message])) ? $lang[$message] : $message;

        // do replacing
        if(is_array($args))
        {
            $text = vsprintf($text, $args);
        }
        
        // Set the global
        $GLOBALS['template_messages'][] = "<div class=\"alert ".$level."\">".$text."</div>";
        return;
    }

/*
| ---------------------------------------------------------------
| Function: load_language_file
| ---------------------------------------------------------------
|
| This method load a specific language file based on the users
| selected language.
|
| @Param: (String) $file - The language file we are loading
| @Return (Array) An array of all the language vars
|
*/
    function load_language_file($file)
    {
        // Load language
        if(isset($_COOKIE['language']))
        {
            $language = $_COOKIE['language'];
        }
        else
        {
            $language = config('default_language');
            $input = load_class('Input');
            $input->set_cookie('language', $language);
            unset($input);
        }
        
        // Init the language class
        $lang = load_class('Language');
        $lang->set_language( $language );
        $array = $lang->load($file, $language, TRUE );
        
        // If we got a false return from the language class, file was not found
        if(!$array)
        {
            // Try and load the default lanuage
            $lang->set_language( $file, config('default_language') );
            $array = $lang->load($file);
            
            // If another false return, could not locate language file
            if(!$array)
            {
                trigger_error('Unable to load the site default or requested language file: '.$language.'/'.$file);
            }
        }
        return $array;	
    }
    

/*
| ---------------------------------------------------------------
| Method: load_module_config()
| ---------------------------------------------------------------
|
| This function is used to load a modules config file, and add
| those config values to the site config.
|
| @Param: (String) $module - Name of the module
| @Param: (String) $filename - name of the file if not 'config.php'
| @Param: (String) $array - If the config vars are stored in an array, whats
|	the array variable name?
| @Return: (None)
|
*/
    function load_module_config($module, $filename = 'config.php', $array = FALSE)
    {	
        // Get our filename and use the load_config method
        $file = APP_PATH . DS .'modules' . DS . $module . DS . 'config' . DS . $filename;
        load_config($file, 'mod', $array);
    }

/*
| ---------------------------------------------------------------
| Method: get_installed_realms()
| ---------------------------------------------------------------
|
| This function is used to return an array of site installed realms.
|
| @Return: (Array) Array of installed realms
|
*/
    function get_installed_realms()
    {
        $load = load_class('Loader');
        $DB = $load->database( 'DB' );
        
        // Build our query
        $query = "SELECT * FROM `pcms_realms`";
        return $DB->query( $query )->fetch_array();
    }
    
/*
| ---------------------------------------------------------------
| Method: get_wowlib_drivers()
| ---------------------------------------------------------------
|
| This function is used to return an array of WoWLib drivers
|
| @Return: (Array) Array of drivers
|
*/
    function get_wowlib_drivers()
    {
        $reallist = FALSE;
        $list = scandir(APP_PATH . DS . 'library' . DS . 'wowlib');
        foreach($list as $file)
        {
            if($file == "." || $file == ".." || $file == "index.html") continue;
            $reallist[] = str_replace(".php", '', $file);
        }
        return $reallist;
    }
// EOF