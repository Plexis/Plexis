<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Module.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Module
 */
namespace Core;

/**
 * The module class is used to hold information about, as well as execute its action
 * methods upon request.
 *
 * @author      Steven Wilson 
 * @package     Core
 */
class Module
{
    /**
     * The module name
     * @var string
     */
    protected $name;
    
    /**
     * The root path to the module
     * @var string
     */
    protected $rootPath;
    
    /**
     * The controller class name
     * @var string
     */
    protected $controller;
    
    /**
     * The method to be called in the controller class
     * @var string
     */
    protected $action;
    
    /**
     * Arrat of parameters to be passed to the action method
     * @var string[]
     */
    protected $params;
    
    /**
     * Used to determine if the module is a Plexis core module
     * @var bool
     */
    protected $isCoreModule;
    
    /**
     * If the module.xml has been requested, its XMLObject is stored here.
     * @var Object
     */
    protected $xml;
    
    /**
     * Module Constructor.
     *
     * @param string $path The root path to the module directory (can be a relative path from cms root)
     * @param string $controller The controller name to dispatch
     * @param string $method The method name to dispatch in the controller.
     * @param string[] $params The arguments to be passed to the method when dispatched.
     */
    public function __construct($path, $controller = null, $method = null, $params = array())
    {
        // Make sure the module path is valid
        $this->rootPath = truePath($path);
        if(!is_dir($this->rootPath))
            throw new \ModuleNotFoundException("Module path '". $this->rootPath ."' does not exist");
        
        // Set internal variables
        $this->name = basename($this->rootPath);
        $this->controller = ucfirst($controller);
        $this->action = $method;
        $this->params = $params;
        $this->isCoreModule = (path( SYSTEM_PATH, 'modules' ) == dirname($this->rootPath));
    }
    
    /**
     * This method executes the controller and action
     *
     * @throws \Exception Thrown ifthe controller or action variables were never set, or empty.
     * @throws \ControllerNotFoundException when the controller file cant be found
     * @throws \MethodNotFoundException when the controller doesnt have the given action,
     *   or the action method is not a public method
     *
     * @return object Returns the module controller object
     */
    public function invoke()
    {
        // Make sure the controller is not empty
        if(empty($this->controller))
            throw new Exception("No controller defined");
         
        // Also make sure the action method is not empty
        if(empty($this->action))
            throw new Exception("No action defined");
            
        // Build path to the controller
        $file = path($this->rootPath, 'controllers', $this->controller .'.php');
        if(!file_exists($file))
            throw new \ControllerNotFoundException('Could not find the controller file "'. $file .'"');
        
        // Load our controller file, and construct the module.
        require_once $file;
        $Dispatch = new $this->controller($this);
        
        // Create a reflection of the controller method
        try {
            $Method = new \ReflectionMethod($Dispatch, $this->action);
        }
        catch(\ReflectionException $e) {
            throw new \MethodNotFoundException("Controller \"{$this->controller}\" does not contain the method \"{$this->action}\"");
        }
        
        // If the method is not public, throw MethodNotFoundException
        if(!$Method->isPublic())
            throw new \MethodNotFoundException("Method \"{$this->action}\" is not a public method, and cannot be called via URL.");
         
        // Invoke the module controller and action
        $Method->invokeArgs($Dispatch, $this->params);
        return $Dispatch;
    }
    
    /**
     * Returns whehter the module is a Plexis Core Module
     *
     * @return bool
     */
    public function isCoreModule()
    {
        return $this->isCoreModule;
    }
    
    /**
     * Returns the modules name
     *
     * @return string
     */
    public function getName() 
    {
        return $this->name;
    }
    
    /**
     * Returns the path to the modules root folder
     *
     * @return string Returns the set controller path, or false 
     *   if the path isnt set
     */
    public function getRootPath() 
    {
        return $this->rootPath;
    }
    
    /**
     * Returns the current set module controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->controller;
    }
    
    /**
     * Returns the current set action (method) to be called when the
     * controller is to be dispatched
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->action;
    }
    
    /**
     * Returns an array of params to be passed to the controller's action 
     * method when dispatched.
     *
     * @return string[]
     */
    public function getActionParams()
    {
        return $this->params;
    }
    
    /**
     * Returns the data stored in the Modules XML file.
     *
     * @return \SimpleXMLElement Returns an object of class SimpleXMLElement with properties 
     *   containing the data held within the XML document
     */
    public function getModuleXml()
    {
        if(empty($this->xml))
            $this->xml = simplexml_load_file($this->rootPath . DS . 'module.xml');
        
        return $this->xml;
    }
    
    /**
     * Sets the controller name, overriding the controller the router finds
     *
     * @param string $name The name of the controller to load
     *
     * @return void
     */
    public function setControllerName($name)
    {
        $this->controller = ucfirst( $name );
    }
    
    /**
     * Sets the action name, overriding the action the router finds
     *
     * @param string $name The name of the action to load
     *
     * @return void
     */
    public function setActionName($name)
    {
        $this->action = $name;
    }
    
    /**
     * Sets the actions' parameters, overriding the params the router finds
     *
     * @param string[] $params - An array of params to pass to the action
     *
     * @return void
     */
    public function setActionParams($params)
    {
        if(!is_array($params))
            return false;
            
        $this->params = $params;
    }
}