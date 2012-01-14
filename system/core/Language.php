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
    
    // Our selected language
    public $language;

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
        $this->language = strtolower($lang);
    }

/*
| ---------------------------------------------------------------
| Function: load()
| ---------------------------------------------------------------
|
| Loads the lanugage file
|
| @Param: (String) $file - Name of the language file, without the extension
| @Param: (Bool) $return - Set to TRUE to return the $lang array, FALSE
|       to just save the variables here.
| @Return (Mixed) Depends on the $return variable
|
*/
    public function load($file, $lang = NULL)
    {
        // Add the extension, and create our tag
        if($lang == NULL) $lang = $this->language;
        $key = $file .'_'. $lang;
        $file = $file . '.php';

        // Make sure we havent loaded this already
        if(isset($this->language_vars[$key]))
        {
            return $this->language_vars[$key];
        }
        
        // Init our empty variable arrays
        $vars = array();
        $vars2 = array();

        // Load the core language file if it exists
        if(file_exists(SYSTEM_PATH . DS .'language' . DS . $lang . DS . $file))
        {
            $vars = include(SYSTEM_PATH . DS .'language' . DS . $lang . DS . $file);
            if(!is_array($vars)) return FALSE;
        }

        // Next we load the application file, allows overriding of the core one
        if(file_exists(APP_PATH . DS .'language' . DS . $lang . DS . $file))
        {
            $vars2 = include(APP_PATH . DS .'language' . DS . $lang . DS . $file);
            if(!is_array($vars2)) return FALSE;
        }
        
        // Merge if both the app and core had the same filename
        $vars = array_merge($vars, $vars2);

        // Without a return, we need to store what we have here.
        $this->loaded_files[] = $file;
        $this->language_vars[$key] = $vars;

        // Init the return
        return ( !empty($vars) ) ? $vars : FALSE;
    }

/*
| ---------------------------------------------------------------
| Function: get()
| ---------------------------------------------------------------
|
| Returns the variable from the config array
|
| @Param: (String) $var - the key of the lang array value
| @Param: (String) $file - The filename the var belongs in (no Ext)
| @Return (Mixed) FALSE if the var is unset, or the string otherwise
|
*/
    public function get($var, $file = NULL)
    {
        // Check to see that we loaded something first
        if(empty( $this->language_vars )) return FALSE;
        
        // Determine our language variable filename if not givin
        if($file == NULL) $file = end( $this->loaded_files );
        
        // Build out lang var key
        $key = $file .'_'. $this->language;
        
        // check to see if our var is set... if not, try to load it first
        if( !isset($this->language_vars[$key]) ) $this->load($file);
        
        // Attempt to load the actual language var now
        if(isset($this->language_vars[$key][$var]))
        {
            return $this->language_vars[$key][$var];
        }
        
        // We tried everything :(
        return FALSE;
    }
}
// EOF