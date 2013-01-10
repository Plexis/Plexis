<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Autoloader.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Autoloader
 */
namespace Core;
 
/**
 * This class is an advanced autoloader for missing class references.
 * Able to register namespace specific paths, as well as prefix
 * specific paths.
 *
 * This class was taken from the Symfony2 package, and re-written for
 * the use of Plexis CMS.
 *
 * @author      Fabien Potencier <fabien.potencier@symfony-project.org>
 * @author      Steven Wilson 
 * @package     Core
 */
class AutoLoader
{
    /**
     * A bool that states whether the Autoloader is registered with spl_autoload
     * @var bool
     */
    protected static $isRegistered = false;
    
    /**
     * An array of registered paths
     * @var string[]
     */
    protected static $paths = array();
    
    /**
     * An array of registered namepace => path
     * @var string[]
     */
    protected static $namespaces = array();
    
    /**
     * An array of registered prefix => path
     * @var string[]
     */
    protected static $prefixes = array();
    
    /**
     * Registers the AutoLoader class with spl_autoload. Multiple
     * calls to this method will not yeild any additional results.
     *
     * @return void
     */
    public static function Register()
    {
        if(self::$isRegistered) return;
        
        spl_autoload_register('Core\AutoLoader::LoadClass');
        
        self::$isRegistered = true;
    }
    
    /**
     * Un-Registers the AutoLoader class with spl_autoload
     *
     * @return void
     */
    public static function UnRegister()
    {
        if(!self::$isRegistered) return;
        
        spl_autoload_unregister('Core\AutoLoader::LoadClass');
        
        self::$isRegistered = false;
    }
    
    /**
     * Registers a path for the autoload to search for classes. Namespaced
     * and prefixed registered paths will be searched first if the class
     * is namespaced, or prefixed.
     *
     * @param string $path Full path to search for a class
     * @return void
     */
    public static function RegisterPath($path)
    {
        if(array_search($path, self::$paths) === false)
            self::$paths[] = $path;
    }
    
    /**
     * Registers a path for the autoloader to search in when searching
     * for a specific namespaced class. When calling this method more
     * than once with the same namespace, the path(s) will just be added 
     * to the current ruuning list of paths for that namespace
     *
     * @param string $namespace The namespace we are registering
     * @param string|array $path Full path, or an array of paths
     *   to search for the namespaced class'.
     * @return void
     */
    public static function RegisterNamespace($namespace, $path)
    {
        if(isset(self::$namespaces[$namespace]))
            self::$namespaces[$namespace] = array_merge(self::$namespaces[$namespace], (array) $path);
        else
            self::$namespaces[$namespace] = (array) $path;
    }
    
    /**
     * Registers a path for the autoload to search for when searching
     * for a prefixed class. When calling this method more than once 
     * with the same prefix, the path(s) will just be added to the current 
     * ruuning list of paths for that prefix
     *
     * @param string $prefix The class prefix we are registering
     * @param string|array $path Full path, or an array of paths
     *   to search for the prefixed class'
     * @return void
     */
    public static function RegisterPrefix($prefix, $path)
    {
        if(isset(self::$prefixes[$prefix]))
            self::$prefixes[$prefix] = array_merge(self::$prefixes[$prefix], (array) $path);
        else
            self::$prefixes[$prefix] = (array) $path;
    }
    
    /**
     * Returns an array of all registered namespaces as keys, and an array
     * of registered paths for that namespace as values
     *
     * @return string[]
     */
    public static function GetNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Returns an array of all registered prefixes as keys, and an array
     * of registered paths for that prefix as values
     *
     * @return string[]
     */
    public static function GetPrefixes()
    {
        return $this->prefixes;
    }
    
    /**
     * Method used to search all registered paths for a missing class
     * reference (used by the spl_autoload method)
     *
     * @param string $class The class being loaded
     * @return Bool Returns TRUE if the class is found, and file was
     *   included successfully.
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
// EOF