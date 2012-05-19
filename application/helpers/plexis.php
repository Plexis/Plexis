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
        $text = ($lang == FALSE || !isset($lang[$message])) ? $message : $lang[$message];

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
| Function: selected_language
| ---------------------------------------------------------------
|
| This method returns the users selected language
|
| @Return (String) The language string
|
*/
    function selected_language()
    {
        // Load language
        $input = load_class('Input');
        $language = $input->cookie('language', TRUE);
        
        //Load the default language if the user hasnt selected a language yet
        if($language == FALSE)
        {
            $language = config('default_language');
            $input->set_cookie('language', $language);
        }
        
        // Return the language string
        return $language;	
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
| @Return (Array) An array of all the language vars, or FALSE
|
*/
    function load_language_file($file)
    {
        // Load language, and Language class
        $language = selected_language();
        $lang = load_class('Language');
        
        // Attempt to get the language file variables
        $array = $lang->load($file, $language);
        
        // If we got a false or empty return from the language class, file was not found
        if($array == FALSE)
        {
            // Try and load the default lanuage
            $lang->set_language( config('default_language') );
            $array = $lang->load($file);
        }
        return $array;	
    }
    
/*
| ---------------------------------------------------------------
| Function: get_languages()
| ---------------------------------------------------------------
|
| This function is used to return an array of languages in the 
|   languages folder
|
| @Return: (Array) Array of drivers
|
*/
    function get_languages()
    {
        $reallist = array();
        $list = load_class('Filesystem', 'library')->list_folders(APP_PATH . DS . 'language');
        foreach($list as $file)
        {
            $reallist[] = $file;
        }
        return $reallist;
    }
    
/*
| ---------------------------------------------------------------
| Function: language_exists()
| ---------------------------------------------------------------
|
| This function is used to return if a language exists or not
|
| @Return: (Bool)
|
*/
    function language_exists($lang)
    {
        $list = load_class('Filesystem', 'library')->list_folders(APP_PATH . DS . 'language');
        foreach($list as $file)
        {
            if($lang == $file) return true;
        }
        return false;
    }
    
/*
| ---------------------------------------------------------------
| Function: default_language()
| ---------------------------------------------------------------
|
| This function checks if the default language if it exists, or
| the first found language if it does not
|
| @Return: (String) A language name
|
*/
    function default_language()
    {
        $default = config('default_language');
        $list = load_class('Filesystem', 'library')->list_folders(APP_PATH . DS . 'language');
        return (in_array($default, $list)) ? $default : $list[0];
    }
    
/*
| ---------------------------------------------------------------
| Function: get_port_status()
| ---------------------------------------------------------------
|
| This function is used to get the port status of a domain/port
|
| @Return: (Bool) Returns TRUE if the domain us up, FALSE otherwise
|
*/
    function get_port_status($host, $port = 80, $timeout = 3)
    {
        // Load the debug class and disable error reporting
        $Debug = load_class('Debug');
        $Debug->error_reporting( FALSE );
        
        // OPen the connections
        $handle = fsockopen($host, $port, $errno, $errstr, $timeout);
        
        // Restore error reporting
        $Debug->error_reporting( TRUE );
        return ($handle == FALSE) ? FALSE : TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: get_realm_cookie()
| ---------------------------------------------------------------
|
| This function returns the users selected realm from his cookie
|
| @Return: (AInt) The Realm ID
|
*/    
    function get_realm_cookie()
    {
        // Load the input class
        $input = load_class('Input');
        $return = $input->cookie('realm_id', TRUE);
        if($return != FALSE)
        {
            // We need to make sure the realm is still installed
            $DB = load_class('Loader')->database( 'DB' );
            $query = "SELECT `name` FROM `pcms_realms` WHERE `id`=?";
            $result = $DB->query( $query, array($return) )->fetch_column();
            
            // If false, Hard set the cookie to default
            if($result == FALSE) goto SetDefault;
        }
        else
        {
            SetDefault:
            {
                // Hard set the cookie to default
                $return = config('default_realm_id');
                $input->set_cookie('realm_id', $return);
                $_COOKIE['realm_id'] = $return;
            }
        }
        return $return;
    }

/*
| ---------------------------------------------------------------
| Function: get_installed_realms()
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
| Function: realm_installed()
| ---------------------------------------------------------------
|
| This function is used to find out if a realm is installed based on ID
|
| @Return: (Bool) True if the realm is installed, FALSE otherwise
|
*/
    function realm_installed($id)
    {
        $load = load_class('Loader');
        $DB = $load->database( 'DB' );
        
        // Build our query
        $query = "SELECT `name` FROM `pcms_realms` WHERE `id`=?";
        $result = $DB->query( $query, array($id) )->fetch_column();
        
        // Make our return
        if($result == FALSE)
        {
            return FALSE;
        }
        return TRUE;
    }
    
/*
| ---------------------------------------------------------------
| Function: get_realm()
| ---------------------------------------------------------------
|
| This function is used to fetch the realm info based on ID
|
| @Return: (Array) Array of Realm information, FALSE otherwise
|
*/
    function get_realm($id)
    {
        $load = load_class('Loader');
        $DB = $load->database( 'DB' );
        
        // Build our query
        $query = "SELECT * FROM `pcms_realms` WHERE `id`=?";
        $result = $DB->query( $query, array($id) )->fetch_row();
        
        // Make our return
        return $result;
    }
    
/*
| ---------------------------------------------------------------
| Function: get_realm_status()
| ---------------------------------------------------------------
|
| This function is used to return an array of site installed realms.
|
| @Param: (int) $id: The ID of the realm, 0 for an array or all
| @Return: (Array) Array of installed realms
|
*/
    function get_realm_status($id = 0, $cache_time = 300)
    {
        // Check the cache to see if we recently got the results
        $load = load_class('Loader');
        $Cache = $load->library('Cache');
        
        // See if we have cached results
        $result = $Cache->get('realm_status_'.$id);
        if($result == FALSE)
        {
            // If we are here, then the cache results were expired
            $Debug = load_class('Debug');
            $DB = $load->database( 'DB' );
            
            // All realms?
            if($id == 0)
            {
                // Build our query
                $query = "SELECT `id`, `name`, `address`, `port` FROM `pcms_realms`";
            }
            else
            {
                $query = "SELECT `id`, `name`, `address`, `port` FROM `pcms_realms` WHERE `id`=?";
            }
            
            // fetch the array of realms
            $realms = $DB->query( $query )->fetch_array();
            
            // Dont log errors
            $Debug->silent_mode(true);
            
            // Loop through each realm, and get its status
            foreach($realms as $key => $realm)
            {
                $handle = fsockopen($realm['address'], $realm['port'], $errno, $errstr, 1);
                if(!$handle)
                {
                    $realms[$key]['status'] = 0;
                }
                else
                {
                    $realms[$key]['status'] = 1;
                }
            }
            
            // Re-enable errors, and Cache the results for 5 minutes
            $Debug->silent_mode(false);
            $Cache->save('realm_status_'.$id, $realms, $cache_time);
            return $realms;
        }
        return $result;
    }
    
/*
| ---------------------------------------------------------------
| Function: get_wowlib_drivers()
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
        $path = APP_PATH . DS . 'wowlib'. DS . config('emulator');
        $list = load_class('Filesystem', 'Library')->list_folders($path);
        foreach($list as $file)
        {
            $reallist[] = $file;
        }
        return $reallist;
    }
    
/*
| ---------------------------------------------------------------
| Function: load_module_config()
| ---------------------------------------------------------------
|
| This function is used to load a modules config file, and add
| those config values to the site config.
|
| @Param: (String) $module - Name of the module
| @Param: (String) $name - Name of the config array key for this
| @Param: (String) $filename - name of the file if not 'config.php'
| @Param: (String) $array - If the config vars are stored in an array, whats
|	the array variable name?
| @Return: (None)
|
*/
    function load_module_config($module, $name = 'mod', $filename = 'config.php', $array = FALSE)
    {	
        // Get our filename and use the load_config method
        $file = APP_PATH . DS .'modules' . DS . $module . DS . 'config' . DS . $filename;
        load_config($file, $name, $array);
    }

/*
| ---------------------------------------------------------------
| Function: get_modules()
| ---------------------------------------------------------------
|
| This function is used to return an array of modules in the modules
| folder
|
| @Return: (Array) Array of modules
|
*/
    function get_modules()
    {
        $reallist = array();
        $list = load_class('Filesystem', 'library')->list_folders(APP_PATH . DS . 'modules');
        foreach($list as $file)
        {
            $reallist[] = $file;
        }
        return $reallist;
    }

/*
| ---------------------------------------------------------------
| Function: get_installed_modules()
| ---------------------------------------------------------------
|
| This function is used to return an array of site installed modules.
|
| @Return: (Array) Array of installed modules
|
*/
    function get_installed_modules()
    {
        $load = load_class('Loader');
        $DB = $load->database( 'DB' );
        
        // Build our query
        $query = "SELECT * FROM `pcms_modules`";
        return $DB->query( $query )->fetch_array();
    }
    
/*
| ---------------------------------------------------------------
| Function: module_installed()
| ---------------------------------------------------------------
|
| This function is used to find out if a module is installed based 
| on the module name
|
| @Return: (Bool) True if the module is installed, FALSE otherwise
|
*/
    function module_installed($name)
    {
        $load = load_class('Loader');
        $DB = $load->database( 'DB' );
        
        // Build our query
        $query = "SELECT `uri` FROM `pcms_modules` WHERE `name`=?";
        $result = $DB->query( $query, array($name) )->fetch_column();
        return ($result == FALSE) ? FALSE : TRUE;
    }
    
/*
| ---------------------------------------------------------------
| Function: get_templates()
| ---------------------------------------------------------------
|
| This function is used to return an array of template fodlers 
|   in the templates folder
|
| @Return: (Array) Array of template names
|
*/
    function get_templates()
    {
        $reallist = array();
        $list = load_class('Filesystem', 'library')->list_folders(APP_PATH . DS . 'templates');
        foreach($list as $file)
        {
            $reallist[] = $file;
        }
        return $reallist;
    }

/*
| ---------------------------------------------------------------
| Function: get_installed_templates()
| ---------------------------------------------------------------
|
| This function is used to return an array of site installed templates.
|
| @Return: (Array) Array of installed templates
|
*/
    function get_installed_templates()
    {
        $load = load_class('Loader');
        $DB = $load->database( 'DB' );
        
        // Build our query
        $query = "SELECT * FROM `pcms_templates` WHERE `status` = 1";
        return $DB->query( $query )->fetch_array();
    }
    
/*
| ---------------------------------------------------------------
| Function: template_installed()
| ---------------------------------------------------------------
|
| This function is used to find out if a templates is installed based 
| on the templates name
|
| @Return: (Bool) True if the template is installed, FALSE otherwise
|
*/
    function template_installed($name)
    {
        $load = load_class('Loader');
        $DB = $load->database( 'DB' );
        
        // Build our query
        $query = "SELECT `status` FROM `pcms_templates` WHERE `name`=?";
        $result = $DB->query( $query, array($name) )->fetch_column();
        return ($result == FALSE) ? FALSE : TRUE;
    }
    
/*
| ---------------------------------------------------------------
| Function: get_emulators()
| ---------------------------------------------------------------
|
| This function is used to return an array of modules in the modules
| folder
|
| @Return: (Array) Array of emulators
|
*/
    function get_emulators()
    {
        $reallist = array();
        $list = load_class('Filesystem', 'library')->list_folders(APP_PATH . DS . 'wowlib');
        foreach($list as $file)
        {
            $reallist[] = $file;
        }
        return $reallist;
    }

/*
| ---------------------------------------------------------------
| Function: get_secret_questions()
| ---------------------------------------------------------------
|
| This function is used to return an array of WoWLib drivers
|
| @Param: (String) $type: Either return as an 'array' or 'string'
| @Param: (Bool) $notags: Return without option tags?
| @Return: (Array / String) Array or String of quertions wrapped in option tags
|
*/    
    function get_secret_questions($type = 'string', $notags = FALSE)
    {
        // create our return array and load the secret questions language file
        ($type == 'string') ? $return = '' : $return = array();
        $vars = load_language_file('secret_questions');
        
        // Add option tags
        foreach($vars as $key => $q)
        {
            if($type == 'string')
            {
                $return .= "<option value='".$key."'>".$q."</option>\n";
            }
            else
            {
                if($notags == TRUE)
                {
                    $return[$key] = $q;
                }
                else
                {
                    $return[$key] = "<option value='".$key."'>".$q."</option>";
                }
            }
        }
        
        // Return the questions
        return $return;
    }
    
/*
| ---------------------------------------------------------------
| Function: log_action
| ---------------------------------------------------------------
|
| This function is used to exctract a specific piece of the url.
|
| @Param: (String) $id: The username
| @Param: (String) $desc: The action description
|
*/

    function log_action($id, $desc)
    {
        $DB = load_class('Loader')->database('DB');
        return $DB->insert('pcms_admin_logs', array('username' => $id, 'desc' => $desc));
    }
    
	
/*
| ---------------------------------------------------------------
| Function: uri_segment
| ---------------------------------------------------------------
|
| This function is used to exctract a specific piece of the url.
|
| @Param: (String) $index: The zero-based index of the url part to return.
| @Return: (String / Null) String containing the specified url part, 
|   null if the index it out of bounds of the array.
|
*/

    function uri_segment($index)
    {
        return load_class('Router')->get_uri_segement($index);
    }
    
/*
| ---------------------------------------------------------------
| Function: real_ver
| ---------------------------------------------------------------
|
| This function is used to convert float values (in string format)
| into REAL floats... (IE: .10 is greater then .1, which is not
| the case in normal float comparisons... (.1 becomes .01, and
| .10 becomes .1... 1.2.1 becomes 1.0201)
|
|
*/

    function real_ver($ver)
    {
        // First, conver to array by periods
        $ver_arr = explode(".", $ver);
	
        $i = 1;
        $result = 0;
        foreach($ver_arr as $vbit) 
        {
            $result += $vbit * $i;
            $i = $i / 100;
        }
        return $result;
    }
// EOF