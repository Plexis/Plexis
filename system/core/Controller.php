<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Controller.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Controller
 */
namespace Core;

// Bring some classes into scope
use \Plexis;
use \Library\Auth;
use \Library\Template;
use \Library\View;
use \Library\ViewNotFoundException;


/**
 * Class used to assist modules, by providing useful methods.
 *
 * This class is to be extended by modules, in order to provide some common
 * and useful methods for the child class.
 *
 * @author      Steven Wilson 
 * @package     Core
 */
class Controller
{
    /**
     * The root path to the module extending this class
     * @var string
     */
    protected $modulePath;
    
    /**
     * The http path to the module's root folder
     * @var string
     */
    protected $moduleUri;
    
    /**
     * The child module name
     * @var string
     */
    protected $moduleName;
    
    /**
     * Sets up the correct $modulePath and $moduleName variables
     *
     * @param string $Module The Module object of the child Module. Not to be
     *   confused with the child controller, but the argument passed to the chile
     *   controller.
     *
     * @return void
     */
    public function __construct($Module) 
    {
        // Define all our paths for this module
        $this->moduleName = $Module->getName();
        $this->modulePath = $Module->getRootPath();
        $this->moduleUri = str_replace(array(ROOT, DS), array('', '/'), $this->modulePath);
        
        // Disable template rendering in Ajax mode
        if(Request::IsAjax())
            Plexis::RenderTemplate(false);
    }
    
    /**
     * Loads a model for the child controller.
     *
     * The model will be searched for in the modules "models" folder. The
     * result will also be stored in a class variable, the name of the class:
     * "$this->{$name}".
     *
     * @param string $name The modal name to load
     * @param mixed[] $params An array or parameters to pass to the constructor.
     *   Default empty array.
     *
     * @return object|bool The constructed modal object, or false if the model 
     *   could not be located.
     */
    protected function loadModel($name, $params = array())
    {
        // Get our path
        $path = path( $this->modulePath, 'models', $name .'.php');
        
        // Check for the files existance
        if(!file_exists($path))
            return false;
            
        // Load the file
        require $path;
        
        // Init a reflection clas
        $class = false;
        try {
            if(!empty($params))
            {
                $Reflection = new \ReflectionClass($name);
                if($Reflection->hasMethod('__construct'))
                    $class = $Reflection->newInstanceArgs($params);
                else
                    $class = new $name();
            }
            else
                $class = new $name();
        }
        catch(\ReflectionException $e) {}
        
        // Set the model as a class variable
        $this->{$name} = $class;
        
        return $class;
    }
    
    /**
     * Loads a helper file from the modules helper folder
     *
     * @param string $name The helper name to load (no file extension)
     *
     * @return bool Returns true if the helper file was found, false otherwise
     */
    protected function loadHelper($name) 
    {
        // Get our path
        $path = path( $this->modulePath, 'helpers', $name .'.php');
        
        // Check for the files existance
        if(!file_exists($path))
            return false;
            
        require $path;
        return true;
    }
    
    /**
     * Loads a view file for the child controller (See detailed description)
     *
     * The first path searched is the current template's module/views
     * folder. If the template does not contain a view for the current module,
     * then the modules view folder will be checked... If a view file cannot
     * be located on either of those paths, a ViewNotFoundException will be thrown
     * unless the variable $silence is set to true, in which case a false will be retuned.
     *
     * @param string $name The view filename to load (no extension)
     * @param string $jsFile The name of the views javascript file (located in the
     *   modules JS folder). Leave null for no file, or to use the default template's view
     *   js file.
     *
     * @return \Library\View|bool Returns false if the view file cannot be located,
     *   (and $silence is set to true), a Library\View object otherwise
     */
    protected function loadView($name, $jsFile = null)
    {
        // See if the view file exists in the current template
        $View = false;
        $viewHasJs = false;
        try {
            $View = Template::LoadModuleView($this->moduleName, $name, $viewHasJs);
        }
        catch( ViewNotFoundException $e ) {}
        
        if($View === false)
        {
            // Define full module path to view
            $path = path( $this->modulePath, 'views', $name .'.tpl' );
            
            // Try and load the view, catch the exception
            $View = new View($path);
        }
        
        // Load view JS if there is one
        if(!empty($jsFile) && !$viewHasJs)
            Template::AddJsFile($this->moduleUri .'/js/'. $jsFile .'.js');
        
        return $View;
    }
    
    /**
     * Includes a module's js file in the final layouts head tag
     *
     * @param string $name The  name of the JS file located in the
     *   modules JS folder
     *
     * @return void
     */
    protected function addScript($name)
    {
        Template::AddScriptSrc($this->moduleUri .'/js/'. $name .'.js');
    }
    
    /**
     * Includes a module's css file in the final layouts head tag
     *
     * @param string $name The  name of the CSS file located in the
     *   modules CSS folder
     *
     * @return void
     */
    protected function addStylesheet($name)
    {
        Template::AddStylesheet($this->moduleUri .'/css/'. $name .'.css');
    }
    
    /**
     * Loads a controller from the current modules folder, and returns a new 
     *   instance of that class
     *
     * @param string $name The name of the controller to load. The
     * result will also be stored in a class variable, the name of the class:
     * "$this->{$name}".
     *
     * @return object|bool Returns the constructed controller or false if 
     *   the controller doesnt exist
     */
    protected function loadController($name)
    {
        // Get our path
        $path = path( $this->modulePath, 'controllers', $name .'.php');
        
        // Check for the files existance
        if(!file_exists($path))
            return false;
            
        // Load the file
        require $path;
        
        // Init a reflection class
        $class = $this->{$name} = new $name();
        return $class;
    }
    
    /**
     * Loads a config file from the modules config folder
     *
     * @param string $name The name of the config file to load (no exension)
     * @param string $id The config file id (name of the config, used for 
     *   fetching and setting variables)
     * @param string $arrayName If all the config varaiables are in an array,
     *   what is the name of the array?
     *
     * @return bool Returns false if the file cannot be read or located, true
     *   otherwise
     */
    protected function loadConfig($name, $id, $arrayName = false)
    {
        // Get our path
        $path = path( $this->modulePath, 'config', $name .'.php');
        $result = false;
        try {
            Config::Load($path, $id, $arrayName);
            $result = true;
        }
        catch(\FileNotFoundException $e) {}
        
        return $result;
    }
    
    /**
     * When called, if the user is not logged in, the login screen will be shown.
     *
     * NOTE: This method will stop execution of the current request if the user
     * is not logged in (Guest), and the current script will stop executing.
     *
     * @param bool $showLogin When set to true, the login screen will be displayed.
     *   If set to false, a 403 "Forbidden" screen is shown instead.
     *
     * @return void
     */
    protected function requireAuth($showLogin = true) 
    {
        if(Auth::IsGuest())
        {
            if($showLogin)
            {
                // Clean all current output
                ob_clean();
                Template::ClearContents();
                
                // Get our login template contents
                $View = Template::LoadPartial("login");
                $View->Set('SITE_URL', Request::BaseUrl());
                Template::Add($View);
                Template::AddScriptSrc("modules/account/js/login.js");
                
                // Render the template, and die
                Template::Render();
                die;
            }
            else
            {
                // Tell plexis to render a 403
                Plexis::Show403();
            }
        }
    }
    
    /**
     * When called, if the user does not have the specified permission, a 403 "forbidden"
     * screen will be displayed, or a redirection will occur (depending on vars).
     *
     * NOTE: This method will stop execution of the current request when called if the user
     * does not have the specified permission, and the current script will stop executing.
     *
     * @param string $name The name of the permission this user is required to have.
     * @param string $uri The redirect URI (or url). If set to false, a 403 "forbidden"
     *   screen will be displayed instead of a redirect.
     *
     * @return void
     */
    protected function requirePermission($name, $uri = false)
    {
        if(!Auth::HasPermission($name))
        {
            if($uri === false)
            {
                // Tell plexis to render a 403
                Plexis::Show403();
            }
            else
            {
                Response::Redirect($uri);
            }
        }
    }
    
    /**
     * Tells the Core Controller whether or not to throw exceptions.
     *
     * @param bool $bool Do we throw exceptions?
     *
     * @return void
     *
     * @todo Finish this option to throw exceptions in the main controller
     */
    protected function throwExceptions($bool) {}
}