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
     * Sets up the correct $modulePath variable
     *
     * @return void
     */
    public function __construct() 
    {
        $this->modulePath = dirname(Dispatch::GetControllerPath());
    }
    
    /**
     * Loads a model for the child controller.
     *
     * The model will be searched for in the modules "model" folder.
     *
     * @param string $name The modal name to load
     * @param mixed[] $params An array or parameters to pass to the constructor.
     *   Default empty array.
     *
     * @return object|bool The constructed modal object, or false if the model 
     *   could not be located.
     */
    public function loadModel($name, $params = array()) {}
    
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
    public function loadHelper($name, $global = false) {}
    
    /**
     * Loads a view file for the child controller, using the modules view path
     *
     * @param string $name The view filename to load
     * @param bool $silence If set to true, This method will return false instead
     *   of throwing a \Library\ViewNotFoundException. Default value is false.
     *
     * @throws \Library\ViewNotFoundException Thrown if $silence is false, and the
     *   view file cannot be found/
     *
     * @return \Library\View|bool Returns false if the view file cannot be located,
     *   a Library\View object otherwise
     */
    public function loadView($name, $silence = false)
    {
        // Define default view value, and define full path to view
        $View = false;
        $path = path( $this->modulePath, 'views', $name );
        
        // Try and load the view, catch the exception
        try {
            $View = new View($path);
        }
        catch( ViewNotFoundException $e ) {
            if(!$silence) throw $e;
        }
        
        return $View;
    }
}