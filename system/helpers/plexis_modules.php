<?php
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