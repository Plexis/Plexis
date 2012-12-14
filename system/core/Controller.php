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
     * The child module name
     * @var string
     */
    protected $moduleName;
    
    /**
     * Sets up the correct $modulePath and $moduleName variables
     *
     * @return void
     */
    public function __construct() 
    {
        $this->modulePath = dirname(Dispatch::GetControllerPath());
        $parts = explode(DS, $this->modulePath);
        $this->moduleName = end($parts);
    }
    
    /**
     * Loads a model for the child controller.
     *
     * The model will be searched for in the modules "models" folder.
     *
     * @param string $name The modal name to load
     * @param mixed[] $params An array or parameters to pass to the constructor.
     *   Default empty array.
     *
     * @return object|bool The constructed modal object, or false if the model 
     *   could not be located.
     */
    public function loadModel($name, $params = array())
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
            $Reflection = new \ReflectionClass($name);
            if($Reflection->hasMethod('__construct') && !empty($params))
                $class = $Reflection->newInstanceArgs($params);
            else
                $class = new $name();
        }
        catch(\ReflectionException $e) {}
        
        return $class;
    }
    
    /**
     * Loads a helper file for the child controller
     *
     * @param string $name The helper filename to load
     * @param bool $global When set to true, the helper will be searched for
     *   in the <ROOT>/system/helpers folder instead of the modules helper
     *   folder. Default value is false.
     *
     * @return bool Returns true if the helper file was found, false otherwise
     */
    public function loadHelper($name, $global = false) 
    {
        // Get our path
        $path = ($global) 
            ? path( SYSTEM_PATH, 'helpers', $name .'.php' ) 
            : path( $this->modulePath, 'helpers', $name .'.php');
        
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
     * @param bool $silence If set to true, This method will return false instead
     *   of throwing a \Library\ViewNotFoundException. Default value is false.
     *
     * @throws \Library\ViewNotFoundException Thrown if $silence is false, and the
     *   view file cannot be found/
     *
     * @return \Library\View|bool Returns false if the view file cannot be located,
     *   (and $silence is set to true), a Library\View object otherwise
     */
    public function loadView($name, $silence = false)
    {
        // See if the view file exists in the current template
        $View = false;
        try {
            $View = Template::LoadView($this->moduleName, $name);
        }
        catch( ViewNotFoundException $e ) {}
        
        if($View === false)
        {
            // Define full module path to view
            $path = path( $this->modulePath, 'views', $name .'.tpl' );
            
            // Try and load the view, catch the exception
            try {
                $View = new View($path);
            }
            catch( ViewNotFoundException $e ) {
                if(!$silence) throw $e;
            }
        }
        
        return $View;
    }
    
    /**
     * Loads a controller from the current modules folder, and returns a new 
     *   instance of that class
     *
     * @param string $name The name of the controller to load
     *
     * @return object|bool Returns the constructed controller or false if 
     *   the controller doesnt exist
     */
    public function loadController($name)
    {
        // Get our path
        $path = path( $this->modulePath, 'controllers', $name .'.php');
        
        // Check for the files existance
        if(!file_exists($path))
            return false;
            
        // Load the file
        require $path;
        
        // Init a reflection class
        return new $name();
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
    public function loadConfig($name, $id, $arrayName = false)
    {
        // Get our path
        $path = path( $this->modulePath, 'config', $name .'.php');
        
        // Load the file
        return Config::Load($path, $name, $arrayName);
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
     *
     * @todo Finish the requireAuth method
     */
    public function requireAuth($showLogin = true) 
    {
        if(Auth::IsGuest())
        {
            if($showLogin)
            {
                // To be Finished
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
    public function requirePermission($name, $uri = false)
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
}