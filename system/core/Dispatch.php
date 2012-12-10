<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Dispatch.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Dispatch
 * @contains    NotFoundException
 * @contains    ControllerNotFoundException
 * @contains    MethodNotFoundException
 */
namespace Core;

/**
 * Responsible for initializing the controller, and
 * calling on the action method.
 *
 * @author      Steven Wilson 
 * @package     Core
 */
class Dispatch
{
    /**
     * The path to the controller directory
     * @var string
     */
    protected static $controllerDir;
    
    /**
     * The controller class name
     * @var string
     */
    protected static $controller;
    
    /**
     * The method to be called in the controller class
     * @var string
     */
    protected static $action;
    
    /**
     * Arrat of parameters to be passed to the action method
     * @var mixed[]
     */
    protected static $params = array();
    
    /**
     * This method executes the controller and action
     *
     * @throws ControllerNotFoundException when the controller file cant be found
     * @throws MethodNotFoundException when the controller doesnt have the given action
     *
     * @return mixed Returns what the action method returned
     */
    public static function Execute()
    {
        // Initiate the requested uri segements into variables
        $controller = (empty(self::$controller)) ? Router::GetController() : self::$controller;
        $action = (empty(self::$action)) ? Router::GetAction() : self::$action;
        $params = (empty(self::$params)) ? Router::GetParams() : self::$params;
        
        // Require the class file if it doesnt exist
        if(!class_exists($controller, false))
        {
            $file = path(self::$controllerDir, $controller .'.php');
            if(!file_exists($file))
                throw new ControllerNotFoundException('Could not find the controller file "'. $file .'"');
            
            require $file;
        }
        
        // Create a reflection of the controller class
        try {
            $Dispatch = new \ReflectionMethod($controller, $action);
        }
        catch(\ReflectionException $e) {
            throw new MethodNotFoundException("Controller \"{$controller}\" does not contain the method \"{$action}\"");
        }
        
        return $Dispatch->invokeArgs(new $controller(), $params);
    }
    
    /**
     * Sets the path to the controller class files.
     *
     * @param string $path The path to the controller files
     *
     * @return void
     */
    public static function SetControllerPath($path)
    {
        self::$controllerDir = $path;
    }
    
    /**
     * Returns the path to the controller class files.
     *
     * @return string Returns the set controller path, or false 
     *   if the path isnt set
     */
    public static function GetControllerPath()
    {
        return (empty(self::$controllerDir)) ? false : self::$controllerDir;
    }
    
    /**
     * Sets the controller name, overriding the controller the router finds
     *
     * @param string $name The name of the controller to load
     *
     * @return void
     */
    public static function SetController($name)
    {
        self::$controller = $name;
    }
    
    /**
     * Sets the action name, overriding the action the router finds
     *
     * @param string $name The name of the action to load
     *
     * @return void
     */
    public static function SetAction($name)
    {
        self::$action = $name;
    }
    
    /**
     * Sets the actions' parameters, overriding the params the router finds
     *
     * @param mixed[] $params - An array of params to pass to the action
     *
     * @return void
     */
    public static function SetParams(array $params)
    {
        self::$params = $params;
    }
}

// Class Exceptions

/**
 * Thrown when the action does not exist in the controller, or the controller
 * class does not exists. This exeption is mainly thrown for a 404
 * @package     Core
 * @subpackage  Exceptions
 * @file        System/Core/Dispatch.php
 * @see         Dispatch::Execute()
 */
class NotFoundException extends \Exception {}

/**
 * Thrown when the controller class does not exist in the controllers path
 * @package     Core
 * @subpackage  Exceptions
 * @file        System/Core/Dispatch.php
 * @see         Dispatch::Execute()
 */
class ControllerNotFoundException extends NotFoundException {}

/**
 * Thrown when the action method does not exist in the controller
 * @package     Core
 * @subpackage  Exceptions
 * @file        System/Core/Dispatch.php
 * @see         Dispatch::Execute()
 */
class MethodNotFoundException extends NotFoundException {}