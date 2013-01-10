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
        $this->controller = ($controller == null) ? ucfirst($this->name) : ucfirst($controller);
        $this->action = ($method == null) ? 'index' : $method;
        $this->params = $params;
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
            throw new \Exception("No controller defined");
         
        // Also make sure the action method is not empty
        if(empty($this->action))
            throw new \Exception("No action defined");
            
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
        if(!is_bool($this->isCoreModule))
            $this->isCoreModule = (path( SYSTEM_PATH, 'modules' ) == dirname($this->rootPath));
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
        $this->action = strtolower($name);
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
    
    /**
     * Installs the module and defines its routes with the router
     *
     * @param bool $override Remove conflicting routes from other modules? 
     * If false, and there is a routing conflict, an \Exception will be thrown. 
     * Otherwise, the old route will be removed, and the new inserted.
     *
     * @throws \Exception Thrown if there is a routing conflict with another
     *   module, and $override is set to false.
     *
     * @return bool Returns true on success, false otherwise
     */
    public function install($override = false) 
    {
        // Check to see if we are installed already
        if($this->isInstalled())
            return true;
            
        // Add Module Routes
        $Routes = new Router\RouteCollection();
        $Routes->addModuleRoutes($this);
        Router::AddRoutes($Routes, $override);
        
        // Register module as installed
        $DB = Database::GetConnection('DB');
        $data = array(
            'name' => $this->name,
            'core_module' => $this->isCoreModule()
        );
        return $DB->insert('pcms_modules', $data);
    }
    
    /**
     * Removes the modules routes, and declares the module as Uninstalled
     *
     * @return bool Returns true if the module was uninstalled. May return
     *   false if the module was never installed in the first place.
     */
    public function uninstall() 
    {
        $DB = Database::GetConnection('DB');
        Router::RemoveModuleRoutes($this->name);
        return $DB->delete('pcms_modules', array('name' => $this->name));
    }
    
    /**
     * Returns whether or not the module is installed in the plexis database.
     *
     * @return bool Returns true if the module is installed, false otherwise.
     */
    public function isInstalled()
    {
        $DB = Database::GetConnection('DB');
        return (bool) $DB->query("SELECT COUNT(`id`) FROM `pcms_modules` WHERE `name`='{$this->name}';")->fetchColumn();
    }
}