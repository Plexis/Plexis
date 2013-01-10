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

/**
 * The Router is used to determine which module and action to load for 
 * the current request. 
 *
 * When called, this object works with the Request object to determine 
 * the current uri, and analyze it to determine which module, controller, 
 * and method to load. This object also handles the adding and removing of
 * routes that are stored in the plexis database.
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
     * The route stack of all defined routes
     * @var Router\RouteCollection
     */
    protected static $RouteCollection;
    
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
            $action = 'index'; // Default Action
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
                $action = 'index'; // Default Action
            }
            
            // $params is what remains in the url array
            $params = $urlArray;
        }
        
        // Try to find a module route for the request
        if(($Mod = self::MatchRoute($module, $action, $params)) !== false)
            return $Mod;
        
        // If there was no route found, then we assume that its a Plexis core module (Might get removed?)
        $controller = (Request::IsAjax()) ? 'Ajax' : $module;
        $path = path( SYSTEM_PATH, 'modules', $module );
        try {
            $Mod = new Module($path, $controller, $action, $params);
        }
        catch( \ModuleNotFoundException $e ) { }
        
        return $Mod;
    }
    
    /**
     * Adds a list new route rules in the database for future route matching
     *
     * @param Router\RouteCollection $routes The route stack container
     * @param bool $remove Remove conflicting results? If false, and there is a
     *   routing conflict, an \Exception will be thrown. Otherwise, 
     *   the old route will be removed, and the new inserted.
     *   
     * @return bool Returns true if successfull, false otherwise.
     */
    public static function AddRoutes( Router\RouteCollection $routes, $remove = false ) 
    {
        // Check for conflicts
        $conflicts = self::GetConflictingRoutes( $routes );
        if(!empty($conflicts))
        {
            // Throw exception if remove is false
            if(!$remove)
            {
                $convert = array();
                foreach($conflicts as $c)
                    $convert[] = "{$c['module_uri']}/{$c['action_uri']}";
                
                $m = "Could not add route because a routing conflict was detected for uri: ('". implode(', ', $convert) ."')";
                throw new \Exception($m);
            }
            
            // remove conflicting routes
            foreach($conflicts as $c)
            {
                $where = "`module_param`='". $c['module_uri'] ."' AND `action_param`='". $c['action_uri'] ."'";
                self::$DB->delete('pcms_routes', $where);
            }
        }
        
        return (bool) self::$DB->exec($routes->toSql());
    }
    
    /**
     * Removes a defined route from the database
     *
     * @param string $reqModule The module URI segement
     * @param string $reqAction The action URI segement
     *
     * @return bool Returns true if any results were removed, or false
     *   if there was no rows removed
     */
    public static function RemoveRoute($reqModule, $reqAction) 
    {
        $where = "`module_param`='". $reqModule ."' AND `action_param`='". $reqAction ."'";
        return (bool) self::$DB->delete('pcms_routes', $where);
    }
    
    /**
     * Removes all routes associated with a specified module.
     *
     * @param string $module The module name
     *
     * @return bool Returns true if any results were removed, or false
     *   if there was no rows removed
     */
    public static function RemoveModuleRoutes($module) 
    {
        return (bool) self::$DB->delete('pcms_routes', "`module`='". $module ."'");
    }
    
    /**
     * Returns an array of conflicting routes with a Route stack, and the
     * current defined routes
     *
     * @param Router\RouteCollection $routes A routes stack to check for.
     *
     * @return array[] Returns a 2 dimensional array. Each index is an array of
     *      array('module_uri' -> module uri, 'action_uri' -> action uri).
     */
    public static function GetConflictingRoutes( Router\RouteCollection $routes )
    {
        // This may get large, best to only have to do it once!
        if(empty(self::$RouteCollection))
        {
            self::$RouteCollection = new Router\RouteCollection();
            $query = "SELECT `module_param`, `action_param`, `module`, `controller`, `method`, `core` FROM `pcms_routes`";
            $DBroutes = self::$DB->query($query)->fetchAll();
            foreach($DBroutes as $r)
            {
                self::$RouteCollection->addRouteParams(
                    $r['module'], 
                    $r['module_param'] .'/'. $r['action_param'], 
                    $r['controller'], 
                    $r['method'], 
                    $r['core']
                );
            }
        }
        
        // Define our return stack, and get our array of routes
        $RouteCollection = array();
        $routes = $routes->getRoutes();
        
        // Check each defined route against the current defined routes
        foreach($routes as $k => $r)
        {
            $key = explode('_', $k);
            if(self::$RouteCollection->hasRoute($key[0], $key[1]));
                $RouteCollection[] = array('module_uri' => $key[0], 'action_uri' => $key[1]);
        }
        
        return $RouteCollection;
    }
    
    /**
     * Checks a module and action for a matching route.
     *
     * @param string $module The module URI segement
     * @param string $action The action URI segement
     * @param string[] $params An array of the remaing URI parameters
     *
     * @return \Core\Module|bool Returns false if there is no database route,
     *   or if the module matched does not exist.
     */
    public static function MatchRoute($module, $action, $params = array())
    {
        // Search the database for defined routes
        $query = "SELECT `module`,`controller`,`method`,`core` FROM `pcms_routes` WHERE 
            `module_param`='{$module}' AND (`action_param`='*' OR `action_param`='{$action}') LIMIT 1";
        $route = self::$DB->query($query)->fetchRow();
        
        // Do we have a custom route?
        if(!is_array($route))
            return false;
            
        // Define our Module object constructor args
        $controller = (Request::IsAjax()) ? 'Ajax' : $route['controller'];
        
        // Proccess wildcard controller names
        if($controller == "@action")
        {
            $controller = ($action == 'index') ? ucfirst($route['module']) : ucfirst( strtolower($action) );
            $action = ($route['method'] == '*') ? 'index' : $route['method'];
        }
        else
            $action = ($route['method'] == '*') ? $action : $route['method'];
        
        // Proccess Wildcard methods
        if($route['method'] == "@param")
        {
            if(!empty($params))
            {
                $action = $params[0];
                array_shift($params);
            }
            else
                $action = 'index';
        }
        
        // Define path to our module root
        if($route['core'] == 1)
            $path = path( SYSTEM_PATH, 'modules', $route['module'] );
        else
            $path = path( ROOT, 'third_party', 'modules', $route['module'] );
        
        // Load the module request :p
        $return = false;
        try {
            $return = new Module($path, $controller, $action, $params);
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
        
        // Register the router sub namespace
        AutoLoader::RegisterNamespace('Core\Router', path( SYSTEM_PATH, 'core', 'router' ));
        
        // Create an instance of the XssFilter
        $Filter = new XssFilter();
        
        // Load up our DB connection
        self::$DB = \Plexis::LoadDBConnection();
        
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
            \Plexis::Show404();
            
        // Add trace for debugging
        // \Debug::trace("Url routed successfully. Found controller: ". self::$controller ."; Action: ". self::$action ."; Querystring: ". implode('/', self::$params), __FILE__, __LINE__);
    }
}

// Init the class
Router::Init();

// EOF