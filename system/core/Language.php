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
| Class: Language
| ---------------------------------------------------------------
|
| This class is used to load language files and return lang vars.
| 
*/
namespace System\Core;

class Language
{
    // Array of ur language variables
    protected $language_vars = array();

    // An array of loaded language files
    protected $loaded_files = array();

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
*/
    public function __construct()
    {
        // Set the default Language
        $this->set_language( config('core_language', 'Core') );
    }

/*
| ---------------------------------------------------------------
| Function: set_language()
| ---------------------------------------------------------------
|
| Sets the langauge. Does not reload already loaded files
|
| @Param: (String) $lang - Name of the language we are loading
| @Return (None)
|
*/
    public function set_language($lang)
    {
        // Set the default Language
        $this->Language = strtolower($lang);
    }

/*
| ---------------------------------------------------------------
| Function: load()
| ---------------------------------------------------------------
|
| Loads the lanugage file
|
| @Param: (String) $file - Name of the language file, without the extension
| @Param: (String) $lang - Language we are loading
| @Param: (Bool) $return - Set to TRUE to return the $lang array, FALSE
|       to just save the variables here.
| @Return (Mixed) Depends on the $return variable
|
*/
    public function load($file, $return = FALSE)
    {
        // Add the extension
        $file = $file . '.php';
        
        // Make sure we havent loaded this already
        if(isset($this->loaded_files[$file]))
        {
            return TRUE;
        }
        
        // Make this a bit easier to type
        $lang = $this->Language;
        
        // Load the core language file if it exists
        if (file_exists(SYSTEM_PATH . DS .'language' . DS . $lang . DS . $file))
        {
            $vars = include(SYSTEM_PATH . DS .'language' . DS . $lang . DS . $file);
        }

        // Next we load the application file, allows overriding of the core one
        if(file_exists(APP_PATH . DS .'language' . DS . $lang . DS . $file))
        {
            if(isset($vars))
            {
                $vars2 = include(APP_PATH . DS .'language' . DS . $lang . DS . $file);
            }
            else
            {
                $vars = include(APP_PATH . DS .'language' . DS . $lang . DS . $file);
            }
        }
        
        // Merge if both the app and core had the same filename
        if(isset($vars2))
        {
            $vars = array_merge($vars, $vars2);
        }


        // If the array "$language" is none existant in the language file, we have an error
        if(!isset($vars) || !is_array($vars))
        {
            return FALSE;
        }

        // Do we return the array?
        if($return == TRUE)
        {
            return $vars;
        }

        // Without a return, we need to store what we have here.
        $this->loaded_files[] = $file;
        $this->language_vars = array_merge($this->language_vars, $vars);
        unset($vars);
        return TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: get()
| ---------------------------------------------------------------
|
| Returns the variable from the config array
|
| @Param: (String) $var - the key of the lang array value, needed
|   to be returned
| @Return (Mixed) FALSE if the var is unset, or the Variable otherwise
|
*/
    public function get($var)
    {
        if(isset($this->language_vars[$var]))
        {
            return $this->language_vars[$var];
        }
        return FALSE;
    }
}
// EOF