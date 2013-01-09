<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Router.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Router
 */
namespace Core;

// Bring some classes to scope
use \Plexis;

/**
 * This class is used to determine our controller / action. When called
 * this object works with the Request object to determine the current
 * url, and analyze it to determine which controller, and method the 
 * Dispatch class will use.
 *
 * @author      Steven Wilson 
 * @package     Core
 */
class Router
{
    /**
     * Have we routed the url yet?
     * @var bool
     */
    protected static $routed = false;
    
    /**
     * The module request object
     * @var Module
     */
    protected static $RequestModule;
    
    /**
     * The Plexis Database Object
     * @var \Database\Driver
     */
    protected static $DB;
    
    /**
     * Fetches the current request in a \Core\Module object
     *
     * @return Module Returns a Core\Module object of the current request
     */
    public static function GetRequest()
    {
        return self::$RequestModule;
    }
    
    /**
     * This method analyzes a uri string, and returns a Module object
     * of the routed request.
     *
     * @param string $uri The uri string to be routed.
     *
     * @return Module|bool Returns false if the request leads to a 404.
     */
    public static function RouteString($uri) 
    {
        // Remove any left slashes or double slashes
        $uri = trim( preg_replace('~(/+)~', '/', $uri), '/');
        
        // There is no URI, Lets load our controller and action defaults
        if(empty($uri)) 
        {
            // Set our Controller / Action to the defaults
            $module = Config::GetVar('default_module', 'Plexis'); // Default Module
            $action = Config::GetVar('default_action', 'Plexis'); // Default Action
            $params = array(); // Default query string
        }
        else 
        {
            // We will start by bulding our controller, action, and querystring
            $urlArray = explode("/", $uri);
            $module = $urlArray[0];
            
            // If there is an action, then lets set that in a variable
            array_shift($urlArray);
            if(!empty($urlArray) && !empty($urlArray[0])) 
            {
                $action = $urlArray[0];
                array_shift($urlArray);
            }
            else 
            {
                // If there is no action, load the default 'index'.
                $action = Config::GetVar('default_action', 'Plexis');
            }
            
            // $params is what remains in the url array
            $params = $urlArray;
        }
        
        // Try to find a module route for the request
        if(($Mod = self::MatchRoute($module, $action)) !== false)
        {
            $Mod->setActionParams($params);
            return $Mod;
        }
        
        // If there was no route found, then we assume that its a Plexis core module
        $controller = (Request::IsAjax()) ? 'Ajax' : $module;
        $path = path( SYSTEM_PATH, 'modules', $module );
        try {
            $Mod = new Module($path, $controller, $action);
            $Mod->setActionParams($params);
        }
        catch( \ModuleNotFoundException $e ) { }
        
        return $Mod;
    }
    
    /**
     * Adds a new route rule in the database for future route matching
     *
     * @param Module $module The Module object for the module we are appending routes for
     * @param string $reqModule The request module (first part of the URI)
     * @param string|string[] $reqAction The request action, or an array or request actions.
     *
     * @return void
     */
    public static function AddRoute(Module $module, $reqModule, $reqAction) 
    {
        // format controller name
        $reqModule = ucfirst( strtolower($reqModule) );
    }
    
    /**
     * Checks a module and action for a matching route.
     *
     * @param string $module The requested module
     * @param string $action The requested action
     *
     * @return \Core\Module|bool Returns false if there is no database route,
     *   or if the module matched does not exist.
     */
    public static function MatchRoute($module, $action)
    {
        // Search the database for defined routes
        $query = "SELECT `module`,`controller`,`method` FROM `pcms_routes` WHERE 
            `module_param`='{$module}' AND (`action_param`='*' OR `action_param`='{$action}')";
        $route = self::$DB->query($query)->fetchRow();
        
        // Do we have a custom route?
        if(!is_array($route))
            return false;
            
        // Define our Module object constructor args
        $controller = (Request::IsAjax()) ? 'Ajax' : ucfirst(strtolower($route['controller']));
        $action = ($route['method'] == '*') ? $action : $route['method'];
        $path = path( ROOT, 'third_party', 'modules', $route['module'] );
        
        // Load the module request :p
        $return = false;
        try {
            $return = new Module($path, $controller, $action);
        }
        catch( \ModuleNotFoundException $e ) {}
        
        return $return;
    }
    
    /**
     * Returns an array of routes for a defined module
     *
     * @param string $module The requested module
     *
     * @return array[] Returns a two dimensional array. foreach array
     *   key, the index key is the first 2 parts of the URI routed, and the
     *   the value is an array of ('controller' => controller, 'method' => method).
     *   Returns an empty array if there are not routes for the defined module.
     */
    public static function FetchRoutes($module)
    {
        // Search the database for defined routes
        $return = array();
        $query = "SELECT `module_param`, `action_param`, `controller`, `method` FROM `pcms_routes` WHERE 
            `module`='{$module}'";
        $routes = self::$DB->query($query)->fetchAll();
        
        foreach($routes as $route)
        {
            $key = $route['module_param'] .'/'. $route['action_param'];
            $return[ $key ] = array(
                'controller' => $route['controller'], 
                'method' => $route['method']
            );
        }
        
        return $return;
    }
    
    /**
     * This method analyzes the current URL request, and loads the
     * module in which claims the URL route. This method is called
     * automatically, and will not do anything if called again.
     *
     * @return void
     */
    public static function Init() 
    {
        // Make sure we only route once
        if(self::$routed) return;
        
        // Create an instance of the XssFilter
        $Filter = new XssFilter();
        
        // Load up our DB connection
        self::$DB = Plexis::LoadDBConnection();
        
        // Add trace for debugging
        // \Debug::trace('Routing url...', __FILE__, __LINE__);

        // Process the site URI
        if( !Config::GetVar('enable_query_strings', 'Plexis'))
        {
            // Get our current url, which is passed on by the 'url' param
            $uri = (isset($_GET['uri'])) ? $Filter->clean(Request::Query('uri')) : '';   
        }
        else
        {
            // Define our needed vars
            $c_param = Config::GetVar('controller_param', 'Plexis');
            $a_param = Config::GetVar('action_param', 'Plexis');
            $uri = '';
            
            // Make sure we have a controller at least
            $c = $Filter->clean(Request::Query($c_param));
            if(!empty($c))
            {
                // Get our action
                $a = $Filter->clean(Request::Query($a_param));
                if(empty($a)) 
                    $a = Config::GetVar('default_action', 'Plexis'); // Default Action
                
                // Init the uri
                $uri = $c .'/'. $a;
                
                // Clean the query string
                $qs = $Filter->clean( $_SERVER['QUERY_STRING'] );
                $qs = explode('&', $qs);
                foreach($qs as $string)
                {
                    // Convert this segment to an array
                    $string = explode('=', $string);
                    
                    // Dont add the controller / action twice ;)
                    if($string[0] == $c_param || $string[0] == $a_param) continue;
                    
                    // Append the uri vraiable
                    $uri .= '/'. $string[1];
                }
            }
        }
        
        // Tell the system we've routed
        self::$routed = true;  
        
        if((self::$RequestModule = self::RouteString($uri)) == false)
            Plexis::Show404();
            
        // Add trace for debugging
        // \Debug::trace("Url routed successfully. Found controller: ". self::$controller ."; Action: ". self::$action ."; Querystring: ". implode('/', self::$params), __FILE__, __LINE__);
    }
}

// Init the class
Router::Init();

// EOF