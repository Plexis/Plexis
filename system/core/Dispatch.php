<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Dispatch
| ---------------------------------------------------------------
|
| This class is responsible for initializing the controller, and
| calling on the action method.
|
*/
namespace Core;

class Dispatch
{
    protected static $controllerDir;
    protected static $controller;
    protected static $action;
    protected static $params = array();

/*
| ---------------------------------------------------------------
| Method: Execute()
| ---------------------------------------------------------------
|
| This method executes the controller and action
|
| @Return: (Mixed) value of the method
| @Throws: ControllerNotFoundException when the controller file cant be found
| @Throws: MethodNotFoundException when the controller doesnt have the given action
|
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
        
        // Load the class into a var
        //return call_user_func_array(array(new $controller, $action), $params);
    }
    
/*
| ---------------------------------------------------------------
| Method: SetControllerPath()
| ---------------------------------------------------------------
|
| Sets the path to the controller class files.
|
| @Return: (None)
|
*/
    public static function SetControllerPath($path)
    {
        self::$controllerDir = $path;
    }
    
/*
| ---------------------------------------------------------------
| Method: GetControllerPath()
| ---------------------------------------------------------------
|
| Returns the path to the controller class files.
|
| @Return: (String) returns false if the path isnt set
|
*/
    public static function GetControllerPath()
    {
        return (empty(self::$controllerDir)) ? false : self::$controllerDir;
    }
    
/*
| ---------------------------------------------------------------
| Method: SetController()
| ---------------------------------------------------------------
|
| Sets the controller name, overriding the controller the router finds
|
| @Param: (String) $name - The name of the controller to load
| @Return: (None)
|
*/
    public static function SetController($name)
    {
        self::$controller = $name;
    }
    
/*
| ---------------------------------------------------------------
| Method: SetAction()
| ---------------------------------------------------------------
|
| Sets the action name, overriding the action the router finds
|
| @Param: (String) $name - The name of the action to load
| @Return: (None)
|
*/
    public static function SetAction($name)
    {
        self::$action = $name;
    }
    
/*
| ---------------------------------------------------------------
| Method: SetController()
| ---------------------------------------------------------------
|
| Sets the actions' parameters, overriding the params the router finds
|
| @Param: (Array) $params - An array of params to pass to the action
| @Return: (None)
|
*/
    public static function SetParams(array $params)
    {
        self::$params = $params;
    }
}

// Class Exceptions

class NotFoundException extends \Exception {}

class ControllerNotFoundException extends NotFoundException {}

class MethodNotFoundException extends NotFoundException {}