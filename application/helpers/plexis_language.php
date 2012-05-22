<?php
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
    function selected_language($check = false)
    {
        // Load language
        $input = load_class('Input');
        $language = $input->cookie('language', true);
        
        //Load the default language if the user hasnt selected a language yet
        if($language == false)
        {
            $language = default_language();
            $input->set_cookie('language', $language);
        }
        elseif($check == true)
        {
            // Check and make sure the language is installed
            if(!language_exists($language)) $language = default_language();
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
?>