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
     * The request uri
     * @var string
     */
    protected static $uri;
    
    /**
     * Returns all the url information
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
    public static function RouteString($uri) {}
    
    /**
     * This method analyzes the url to determine the controller / action
     * and query string
     *
     * @return void
     */
    public static function RouteUrl() 
    {
        // Make sure we only route once
        if(self::$routed) return;
        
        // Create an instance of the XssFilter
        $Filter = new XssFilter();
        
        // Add trace for debugging
        // \Debug::trace('Routing url...', __FILE__, __LINE__);

        // Process the site URI
        if( !Config::GetVar('enable_query_strings', 'Plexis'))
        {
            // Get our current url, which is passed on by the 'url' param
            self::$uri = (isset($_GET['uri'])) ? $Filter->clean(Request::Query('uri')) : '';   
        }
        else
        {
            // Define our needed vars
            $c_param = Config::GetVar('controller_param', 'Plexis');
            $a_param = Config::GetVar('action_param', 'Plexis');
            
            // Make sure we have a controller at least
            $c = $Filter->clean(Request::Query($c_param));
            if( !$c )
            {
                self::$uri = '';
            }
            else
            {
                // Get our action
                $a = $Filter->clean(Request::Query($a_param));
                if( !$a ) $a = Config::GetVar('default_action', 'Plexis'); // Default Action
                
                // Init the uri
                self::$uri = $c .'/'. $a;
                
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
                    self::$uri .= '/'. $string[1];
                }
            }
        }
        
        // If the URI is empty, then load defaults
        if(empty(self::$uri)) 
        {
            // Set our Controller / Action to the defaults
            $module = Config::GetVar('default_module', 'Plexis'); // Default Module
            $action = Config::GetVar('default_action', 'Plexis'); // Default Action
            $params = array(); // Default query string
        }
        
        // There is a URI, Lets load our controller and action
        else 
        {
            // Remove any left slashes or double slashes
            self::$uri = ltrim( str_replace('//', '/', self::$uri), '/');

            // We will start by bulding our controller, action, and querystring
            $urlArray = array();
            $urlArray = explode("/", self::$uri);
            $module = $urlArray[0];
            
            // If there is an action, then lets set that in a variable
            array_shift($urlArray);
            if(isset($urlArray[0]) && !empty($urlArray[0])) 
            {
                $action = $urlArray[0];
                array_shift($urlArray);
            }
            
            // If there is no action, load the default 'index'.
            else 
            {
                $action = Config::GetVar('default_action', 'Plexis'); // Default Action
            }
            
            // $params is what remains
            $params = $urlArray;
        }
        
        // Tell the system we've routed
        self::$routed = true;  
        
        // Proccess routes
        $DB = Plexis::LoadDBConnection();
        $query = "SELECT `module`,`controller`,`method` FROM `pcms_routes` WHERE 
            `module_param`='{$module}' AND (`action_param`='*' OR `action_param`='{$action}')";
        $route = $DB->query($query)->fetchRow();
        
        // Do we have a custom route?
        if($route === false)
        {
            // Set static Variables
            $controller = (Request::IsAjax()) ? 'Ajax' : $module;
            $path = path( SYSTEM_PATH, 'modules', $module );
        }
        else
        {
            $controller = (Request::IsAjax()) ? 'Ajax' : ucfirst(strtolower($route['controller']));
            $action = ($route['method'] == '*') ? $action : $route['method'];
            $path = path( ROOT, 'third_party', 'modules', $route['module'] );
        }
        
        // Load the module request :p
        self::$RequestModule = new Module($path, $controller, $action, $params);
        
        // Add trace for debugging
        // \Debug::trace("Url routed successfully. Found controller: ". self::$controller ."; Action: ". self::$action ."; Querystring: ". implode('/', self::$params), __FILE__, __LINE__);
    }
}

// Init the class
Router::RouteUrl();

// EOF