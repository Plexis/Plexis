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
    
    public static function Render($return = false)
    {
        // First, load the template xml config file
        self::LoadThemeConfig();
        
        $c = file_get_contents(self::$themePath . DS . 'layout.tpl');
        
        $c = Parser::Parse($c, array('CONTENTS' => self::$buffer));
        
        if($return)
            return $c;
        else
            echo $c;
    }
    
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
    
    public static function SetThemePath($path)
    {
        // Make sure the path exists!
        if(!file_exists($path))
            throw new InvalidThemePathError('Invalid theme path "'. $path .'"');
            
        self::$themePath = $path;
    }
    
    public static function ClearBuffer()
    {
        self::$buffer = null;
    }
    
    protected static function LoadThemeConfig()
    {
        // Make sure a theme is set
        if(empty(self::$themePath))
            throw new ThemeNotSetError('No theme selected!');
            
        $file = path(self::$themePath, 'theme.xml');
        if(!file_exists($file))
            throw new MissingThemeConfigError('Unable to load theme config file "'. $file .'"');
        
        self::$themeConfig = simplexml_load_file($file);
    }
}

// Exceptions //

class ThemeNotSetError extends \ApplicationError {}

class InvalidThemePathError extends \ApplicationError {}

class InvalidPageContents extends \ApplicationError {}

class MissingThemeConfigError extends \ApplicationError {}