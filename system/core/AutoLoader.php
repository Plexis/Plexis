<?php
/* 
| --------------------------------------------------------------
| Plexis Core
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Copyright:    Copyright (c) 2011-2012, Plexis Dev Team
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: AutoLoader
| ---------------------------------------------------------------
|
| This class is an advanced autolaoder for missing class references.
| Able to register namespace specific paths, as well as prefix
| specific paths.
|
*/
namespace Core;

class AutoLoader
{
    protected static $isRegistered = false;
    protected static $paths = array();
    protected static $namespaces = array();
    protected static $prefixes = array();
    
/*
| ---------------------------------------------------------------
| Method: Init()
| ---------------------------------------------------------------
|
| Registers the AutoLoader class with spl_autoload
|
*/
    public static function Init()
    {
        if(self::$isRegistered) return;
        
        spl_autoload_register('Core\AutoLoader::LoadClass');
        
        self::$isRegistered = true;
    }
    
/*
| ---------------------------------------------------------------
| Method: Register()
| ---------------------------------------------------------------
|
| Registers a path for the autoload to search for classes. Namespaced
| and prefixed registered paths will be searched first if the class
| is namespaced, or prefixed.
|
| @Param: (String) $path - Full path to search for a class
| @Return (None)
|
*/
    public static function Register($path)
    {
        if(array_search($path, self::$paths) !== false)
            return;
        self::$paths[] = $path;
    }
    
/*
| ---------------------------------------------------------------
| Method: RegisterNamespace()
| ---------------------------------------------------------------
|
| Registers a path for the autoloader to search in when searching
| for a specific namespaced class. When calling this method more
| than once with the same namespace, the path(s) will just be added 
| to the current ruuning list of paths for that namespace
|
| @Param: (String) $namespace - The namespace we are registering
| @Param: (String | Array) $path - Full path, or an array of paths
|   to search for the namespaced class'.
| @Return (None)
|
*/
    public static function RegisterNamespace($namespace, $path)
    {
        if(isset(self::$namespaces[$namespace]))
            self::$namespaces[$namespace] = array_merge(self::$namespaces[$namespace], (array) $path);
        else
            self::$namespaces[$namespace] = (array) $path;
    }
    
/*
| ---------------------------------------------------------------
| Method: RegisterPrefix()
| ---------------------------------------------------------------
|
| Registers a path for the autoload to search for when searching
| for a prefixed class. When calling this method more than once 
| with the same prefix, the path(s) will just be added to the current 
| ruuning list of paths for that prefix
|
| @Param: (String) $prefix - The class prefix we are registering
| @Param: (String | Array) $path - Full path, or an array of paths
|   to search for the prefixed class'
| @Return (None)
|
*/
    public static function RegisterPrefix($prefix, $path)
    {
        if(isset(self::$prefixes[$prefix]))
            self::$prefixes[$prefix] = array_merge(self::$prefixes[$prefix], (array) $path);
        else
            self::$prefixes[$prefix] = (array) $path;
    }
    
/*
| ---------------------------------------------------------------
| Method: GetNamespaces()
| ---------------------------------------------------------------
|
| Returns an array of all registered namespaces as keys, and an array
| of registered paths for that namespace as values
|
| @Return (Array)
|
*/
    public function GetNamespaces()
    {
        return $this->namespaces;
    }

/*
| ---------------------------------------------------------------
| Method: GetPrefixes()
| ---------------------------------------------------------------
|
| Returns an array of all registered prefixes as keys, and an array
| of registered paths for that prefix as values
|
| @Return (Array)
|
*/
    public function GetPrefixes()
    {
        return $this->prefixes;
    }
    
/*
| ---------------------------------------------------------------
| Method: LoadClass()
| ---------------------------------------------------------------
|
| Method used to search all registered paths for a missing class
| reference (used by the spl_autoload method)
|
| @Param: (String) $class - The class being loaded
| @Return (Bool) Returns TRUE if the class is found, and file was
|   included successfully.
|
*/
    public static function LoadClass($class)
    {
        // Search class name for a namespace
        if(($pos = strripos($class, '\\')) !== false)
        {
            $namespace = substr($class, 0, $pos);
            $class = substr($class, $pos + 1);
            
            if(isset(self::$namespaces[$namespace]))
            {
                foreach(self::$namespaces[$namespace] as $dir)
                {
                    $file = path($dir, str_replace('_', DS, $class) .'.php');
                    if(file_exists($file))
                    {
                        require $file;
                        return true;
                    }
                }
            }
        }
        
        // If no namespace if found, but we have a possible prefixed class (with _ ), search prefixes
        elseif(($pos = strpos($class, '_')) !== false)
        {
            $prefix = substr($class, 0, $pos);
            if(isset(self::$prefixes[$prefix]))
            {
                foreach(self::$prefixes[$prefix] as $dir)
                {
                    $file = path($dir, str_replace('_', DS, $class) .'.php');
                    if(file_exists($file))
                    {
                        require $file;
                        return true;
                    }
                }
            }
        }
        
        // If all else fails, or no prefix/namespace was found, 
        // check default registered paths
        foreach(self::$paths as $dir)
        {
            $file = path($dir, str_replace(array('_', '\\', '/'), DS, $class) .'.php');
            if(file_exists($file))
            {
                require $file;
                return true;
            }
        }
        
        // If we are here, we didnt find the class :(
        return false;
    }
}

AutoLoader::Init();