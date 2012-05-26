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
        // Make this easy, if its already defined
        if(isset($GLOBALS['language'])) return $GLOBALS['language'];
        
        // Load language, and return it
        return load_class('Language')->selected_language();
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
        // Load Language class, and return the language vars for this file
        return load_class('Language')->load($file);	
    }
    
/*
| ---------------------------------------------------------------
| Function: get_languages()
| ---------------------------------------------------------------
|
| This function is used to return an array of languages in the 
|   languages folder
|
| @Return: (Array) Array of found language folders
|
*/
    function get_languages()
    {
        // Load Language class, and return the found languages
        return load_class('Language')->get_languages();	
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
        // Load Language class, and return if $lang exists
        return load_class('Language')->exists($lang);	
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
        // Load Language class, and return if $lang exists
        return load_class('Language')->default_language();
    }
?>