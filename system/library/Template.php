<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Copyright:    Copyright (c) 2011-2012, Plexis Dev Team
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Template
| ---------------------------------------------------------------
|
| Template engine for the CMS
|
*/
namespace Library;

class Template
{
    protected static $buffer = null;
    protected static $themePath;
    protected static $themeConfig;
    
/*
| ---------------------------------------------------------------
| Method: Render()
| ---------------------------------------------------------------
|
| This method sets variables to be replace in the view
|
| @Param: (Bool) $return - when set to true, final rendered template
|   is returned instead of echo'ing it out.
| @Return (None)
|
*/
    public static function Render($return = false, $loadLayout = true)
    {
        // First, load the template xml config file
        if(empty(self::$themeConfig)) self::LoadThemeConfig();
        
        // Load contents and parse the layout file
        if($loadLayout)
        {
            $c = file_get_contents(self::$themePath . DS . 'layout.tpl');
            $c = Parser::Parse($c, array('CONTENTS' => self::$buffer));
        }
        else
            $c = self::$buffer;
        
        if($return)
            return $c;
        else
            echo $c;
    }
    
/*
| ---------------------------------------------------------------
| Method: Add()
| ---------------------------------------------------------------
|
| Adds more to contents to be added into the contents section of
| the final rendered template.
|
| @Return (None)
| @Throws Library\InvalidPageContents if the contents is niether a string
|   of a sub class of Library\View
|
*/
    public static function Add()
    {
        $parts = func_get_args();
        foreach($parts as $contents)
        {
            // Make sure out contents are valid
            if(!is_string($contents) && !(is_object($contents) && ($contents instanceof View)))
                throw new InvalidPageContents('Page contents must be a string, or an object extending the "View" class');
                
            self::$buffer .= (string) $contents;
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: SetThemePath()
| ---------------------------------------------------------------
|
| Sets the path to the theme, in which holds the layout.tpl file
|
| @Param: (String) $path - the full path to the theme
| @Return (None)
| @Throws Library\InvalidThemePathException
|
*/
    public static function SetThemePath($path)
    {
        // Make sure the path exists!
        if(!file_exists($path))
            throw new InvalidThemePathException('Invalid theme path "'. $path .'"');
            
        self::$themePath = $path;
    }
    
/*
| ---------------------------------------------------------------
| Method: ClearBuffer()
| ---------------------------------------------------------------
|
| Clears the current output buffer of the template
|
| @Return (None)
|
*/
    public static function ClearBuffer()
    {
        self::$buffer = null;
    }
    
/*
| ---------------------------------------------------------------
| Method: LoadThemeConfig()
| ---------------------------------------------------------------
|
| Internal method for loading the theme's config xml file
|
| @Return (None)
| @Throws Library\ThemeNotSetException if the theme isnt set before rendering
| @Throws Library\MissingThemeConfigException if the theme is missing its theme
|   config file (theme.xml)
|
*/
    protected static function LoadThemeConfig()
    {
        // Make sure a theme is set
        if(empty(self::$themePath))
            throw new ThemeNotSetException('No theme selected!');
            
        $file = path(self::$themePath, 'theme.xml');
        if(!file_exists($file))
            throw new MissingThemeConfigException('Unable to load theme config file "'. $file .'"');
        
        self::$themeConfig = simplexml_load_file($file);
    }
}

// Exceptions //

class ThemeNotSetException extends \ApplicationError {}

class InvalidThemePathException extends \ApplicationError {}

class InvalidPageContents extends \ApplicationError {}

class MissingThemeConfigException extends \ApplicationError {}