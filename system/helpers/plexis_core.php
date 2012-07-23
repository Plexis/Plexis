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
        $path = path( ROOT, 'third_party', 'wowlib', 'emulators', config('emulator') );
        $list = load_class('Filesystem', 'Library')->list_folders($path);
        foreach($list as $file)
        {
            $reallist[] = $file;
        }
        return $reallist;
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
        $path = path( ROOT, 'third_party', 'wowlib', 'emulators' );
        $list = load_class('Filesystem', 'library')->list_folders( $path );
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
        // First, convert to array by periods
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