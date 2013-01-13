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
 * The module class is used to hold information about requested modules, 
 *  as well as execute its controller action methods upon request.
 *
 * @author      Steven Wilson 
 * @package     Core
 */
class Module
{
    /**
     * An array of loaded modules
     * @var Module[]
     */
    protected static $modules = array();
    
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
     * If the module.xml has been requested, its XMLObject is stored here.
     * @var Object
     */
    protected $xml;
    
    /**
     * Main method used to fetch and load modules. This method acts
     * like a factory, and stores all loaded modules statically.
     *
     * @param string $name The name of the module folder
     * @return Module Returns a module object
     */
    public static function Get($name)
    {
        if(!isset(self::$modules[$name]))
            self::$modules[$name] = new Module($name);
            
        return self::$modules[$name];
    }
    
    /**
     * Module Constructor. This method should never be called
     * by another library or module, but rather called by the
     * internal Module::Get method
     *
     * @param string $name The name of the module folder
     */
    public function __construct($name)
    {
        // Make sure the module path is valid
        $this->rootPath = truePath("modules/". $name);
        if(!is_dir($this->rootPath))
            throw new \ModuleNotFoundException("Module path '". $this->rootPath ."' does not exist");
            
        // Make sure the xml file exists!
        $xml = $this->rootPath . DS . 'module.xml';
        if(!file_exists($xml))
            throw new \ModuleNotFoundException("Module missing its xml file: '{$xml}'.");
            
        // Load up the xml file
        $this->xml = simplexml_load_file($xml);
        
        // Set internal variables
        $this->name = $name;
    }
    
    /**
     * Invokes a controller and action within the module.
     *
     * @param string $controller The controller name to call. Case Sensative!
     * @param string $action The controller method name to execute. Case IN-sensative.
     * @param string[] $params The parameters to pass to the controller method.
     *
     * @throws \ControllerNotFoundException when the controller file cant be found
     * @throws \MethodNotFoundException when the controller doesnt have the given action,
     *   or the action method is not a public method
     *
     * @return mixed Returns whatever the method returns, Most likely null.
     */
    public function invoke($controller, $action, $params = array())
    {
        // Build path to the controller
        $file = path($this->rootPath, 'controllers', $controller .'.php');
        if(!file_exists($file))
            throw new \ControllerNotFoundException('Could not find the controller file "'. $file .'"');
        
        // Load our controller file, and construct the module.
        require_once $file;
        $Dispatch = new $controller($this);
        
        // Create a reflection of the controller method
        try {
            $Method = new \ReflectionMethod($Dispatch, $action);
        }
        catch(\ReflectionException $e) {
            throw new \MethodNotFoundException("Controller \"{$controller}\" does not contain the method \"{$action}\"");
        }
        
        // If the method is not public, throw MethodNotFoundException
        if(!$Method->isPublic())
            throw new \MethodNotFoundException("Method \"{$action}\" is not a public method, and cannot be called via URL.");
         
        // Invoke the module controller and action
        return $Method->invokeArgs($Dispatch, $params);
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
     * Installs the module and defines its routes with the router
     *
     * @throws \Exception Thrown if there is a routing conflict with another
     *   module, and $override is set to false.
     *
     * @return bool Returns true on success, false otherwise
     */
    public function install() 
    {
        // Check to see if we are installed already
        if($this->isInstalled())
            return true;
            
        // DB connections and xml files
        $Xml = $this->getModuleXml();
        $DB = Database::GetConnection('DB');
        
        // Register module as installed
        $data = array(
            'name' => $this->name,
            'version' => $Xml->info->version
        );
        return $DB->insert('pcms_modules', $data);
    }
    
    /**
     * Removes the module from the database, declaring the module as Uninstalled
     *
     * @return bool Returns true if the module was uninstalled. May return
     *   false if the module was never installed in the first place.
     */
    public function uninstall() 
    {
        $DB = Database::GetConnection('DB');
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
        return (bool) $DB->query("SELECT COUNT(`name`) FROM `pcms_modules` WHERE `name`='{$this->name}';")->fetchColumn();
    }
}