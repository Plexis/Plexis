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

use Core\Router\RouteCollection;
use Core\Router\Route;

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
     * Specified whether the main request was handled
     * @var Module
     */
    protected static $RequestHandled = false;
    
    /**
     * The Plexis Database Object
     * @var \Database\Driver
     */
    protected static $DB;
    
    /**
     * The route stack of all defined routes
     * @var Router\RouteCollection
     */
    protected static $Routes;
    
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
        
        // Load up our DB connection
        self::$DB = \Plexis::LoadDBConnection();
        
        // Load our route collection
        self::$Routes = new RouteCollection();
        $routes = array();
        
        // Search the database for defined routes
        include SYSTEM_PATH . DS .'config'. DS .'routes.php';
        
        // Do we have a custom route?
        if(is_array($routes))
        {
            // Add routes to the collection
            foreach($routes as $match => $routes)
                self::$Routes->addRoute( new Route($match, $routes) );
        }
        
        // Tell the system we've routed
        self::$routed = true;  
    }
    
    /**
     * Executes the main request.
     *
     * @return void
     */
    public static function HandleRequest()
    {
        // Dont handle the request twice
        if(self::$RequestHandled)
            return;
            
        // Create an instance of the XssFilter
        $Filter = new XssFilter();
        
        // Process the site URI
        if( !Config::GetVar('enable_query_strings', 'Plexis'))
        {
            // Get our current url, which is passed on by the 'url' param
            $uri = (isset($_GET['uri'])) ? $Filter->clean(Request::Query('uri')) : '';   
        }
        else
        {
            // Define our needed vars
            $m_param = Config::GetVar('module_param', 'Plexis');
            $c_param = Config::GetVar('controller_param', 'Plexis');
            $a_param = Config::GetVar('action_param', 'Plexis');
            $uri = '';
            
            // Make sure we have a module at least
            $m = $Filter->clean(Request::Query($m_param));
            if(!empty($m))
            {
                // Get our controller
                $c = $Filter->clean(Request::Query($c_param));
                if(!empty($c)) 
                    $uri .= '/'. $c;
                    
                // Get our action
                $a = $Filter->clean(Request::Query($a_param));
                if(!empty($a))
                    $uri .= '/'. $a;
                
                // Clean the query string
                $qs = $Filter->clean( $_SERVER['QUERY_STRING'] );
                $qs = explode('&', $qs);
                foreach($qs as $string)
                {
                    // Convert this segment to an array
                    $string = explode('=', $string);
                    
                    // Dont add the controller / action twice ;)
                    if($string[0] == $m_param || $string[0] == $c_param || $string[0] == $a_param)
                        continue;
                    
                    // Append the uri vraiable
                    $uri .= '/'. $string[1];
                }
            }
        }
        
        // Prevent future requests
        self::$RequestHandled = true;
        return self::Execute($uri);
    }
    
    /**
     * This method analyzes a uri string, and executes the module
     * tied to the route. If the route cannot be parsed, a 404 error
     * will be thrown
     *
     * @param string $route The uri string to be routed.
     * @param bool $isAjax Proccess the route in ajax mode?
     *   If the main request is ajax, then setting this to
     *   true will execute the route as a normal HTTP request.
     *
     * @return void
     */
    public static function Execute($route, $isAjax = null)
    {
        // Route request
        $Mod = self::LoadModule($route, $data);
        if($Mod == false)
        {
            self::Execute('error/404');
            die();
        }
        
        // Define which controller and such we load
        $isAjax = ($isAjax === null) ? Request::IsAjax() : $isAjax;
        $controller = ($isAjax && isset($data['ajax']['controller'])) 
            ? $data['ajax']['controller'] 
            : $data['controller'];
        $action = ($isAjax && isset($data['ajax']['action']))
            ? $data['ajax']['action'] 
            : $data['action'];
        
        // Might move these later
        $GLOBALS['controller'] = ucfirst($Mod->getName());
        $GLOBALS['action'] = $action;
        $GLOBALS['querystring'] = $data['params'];
        
        // Fire the module off
        try {
            $Mod->invoke($controller, $action, $data['params']);
        }
        catch( \MethodNotFoundException $e ) {
            self::Execute('error/404');
        }
        catch( \ControllerNotFoundException $e ) {
            self::Execute('error/404');
        }
    }
    
    /**
     * This method is similar to execute, but does not call on
     * the module to preform any actions. Instead, the data require
     * to correcltly invoke the module, as well as the Core\Module
     * itself is returned.
     *
     * @param string $route The uri string to be routed.
     * @param string[] $data [Reference Variable] This variable will
     *   pass back the request data, such as the controller, action, 
     *   and parameters to be used to invoke the module.
     * @param bool $isAjax Proccess the route in ajax mode?
     *   If the main request is ajax, then setting this to
     *   true will execute the route as a normal HTTP request.
     *
     * @return Module|bool Returns false if the request leads to a 404,
     *   otherwise the module object will be returned.
     */
    public static function Forge($route, &$data = array(), $isAjax = null)
    {
        if(($Mod = self::LoadModule($route, $d)) === false)
            return false;
        
        // Define which controller and such we load
        $isAjax = ($isAjax === null) ? Request::IsAjax() : $isAjax;
        $data['controller'] = ($isAjax && isset($d['ajax']['controller'])) 
            ? $d['ajax']['controller'] 
            : $d['controller'];
        $data['action'] = ($isAjax && isset($d['ajax']['action']))
            ? $d['ajax']['action'] 
            : $d['action'];
        $data['params'] = $d['params'];
        return $Mod;
    }
    
    /**
     * Adds a list new route rules in the database for future route matching
     *
     * @param Router\RouteCollection $routes The route stack container
     *   
     * @return bool Returns true if successfull, false otherwise.
     */
    public static function AddRoutes( RouteCollection $routes ) 
    {
        // Add routes to the collection
        self::$Routes->merge( $routes );
        
        // Write routes file
        $routes = self::$Routes->getRoutes();
        
        // Save the rotues file
        $file = path( SYSTEM_PATH, 'config', 'routes.php' );
        $string = "<?php\n\$routes = ". var_export($routes, true) .";\n?>";
        $string = preg_replace('/[ ]{2}/', "\t", $string);
        $string = preg_replace("/\=\>[ \n\t]+array[ ]+\(/", '=> array(', $string);
        return file_put_contents($file, $string);
    }
    
    /**
     * Removes a defined route from the database
     *
     * @param string $key The routes array key in routes.php
     *
     * @return bool Returns true on success
     */
    public static function RemoveRoute($key) 
    {
        self::$Routes->removeRoute($key);
        
        // Get our new list of routes
        $routes = self::$Routes->getRoutes();
        
        // Save the routes file
        $file = path( SYSTEM_PATH, 'config', 'routes.php' );
        $string = "<?php\n\$routes = ". var_export($routes, true) .";\n?>";
        $string = preg_replace('/[ ]{2}/', "\t", $string);
        $string = preg_replace("/\=\>[ \n\t]+array[ ]+\(/", '=> array(', $string);
        return file_put_contents($file, $string);
    }
    
    /**
     * Returns the route collection containing all defined routes.
     *
     * @return Router\RouteCollection
     */
    public static function FetchRoutes()
    {
        return self::$Routes;
    }
    
    /**
     * Checks a module and action for a matching route.
     *
     * @param string $route The route to map for a module
     * @param string[] $data [Reference Variable] This variable will
     *   pass back the request data, such as the controller, action, 
     *   and parameters to be used to invoke the module.
     *
     * @return \Core\Module|bool Returns false if there is no database route,
     *   or if the module matched does not exist.
     */
    protected static function LoadModule($route, &$data)
    {
        // There is no URI, Lets load our controller and action defaults
        $route = trim($route);
        if(empty($route))
        {
            $route = Config::GetVar('default_module', 'Plexis'); // Default Module
        }
        else
        {
            // We are note allowed to call certain methods
            $parts = explode('/', $route);
            if(isset($parts[2]) && strncmp($parts[2], '_', 1) == 0)
                return false;
        }
        
        // Format URI
        $route = trim( strtolower($route) );
        $Mod = false;
        
        // Try to find a module route for the request
        if(self::$Routes->hasRoute($route, $data))
        {
            // Check for a routes
            try {
                $Mod = Module::Get( $data['module'] );
            }
            catch( \ModuleNotFoundException $e ) {}
            
            // Does module exist?
            if($Mod == false)
                return false;
                
            // Is the module installed?
            if(!$Mod->isInstalled())
                return false;
                
            // Is the module installed?
            if(!$Mod->isInstalled())
                return false;
        }
        else
        {
            // Get our module name
            $parts = explode('/', $route);
            $module = $parts[0];
            
            // Check for a routes
            try {
                $Mod = Module::Get( $module );
            }
            catch( \ModuleNotFoundException $e ) {}
            
            // Does module exist?
            if($Mod == false)
                return false;
                
            // Is the module installed?
            if(!$Mod->isInstalled())
                return false;
                
            // Load the routes file if it exist
            $path = path( $Mod->getRootPath(), 'config', 'routes.php' );
            if(file_exists($path))
            {
                $routes = array();
                include $path;
                if(is_array($routes))
                {
                    $Rc = new RouteCollection();
                    foreach($routes as $match => $r)
                        $Rc->addRoute( new Route($match, $r) );
                        
                    if(!$Rc->hasRoute($route, $data))
                        goto NoModuleRoute;
                }
                else
                {
                    goto NoModuleRoute;
                }
            }
            else
            {
                // Go to for not having a module route defined
                NoModuleRoute:
                {
                    // Is this an error?
                    if(strpos('error/', $route) !== false)
                    {
                        switch($route)
                        {
                            case "error/404":
                                die('404 Not Found');
                            case "error/403":
                                die('Forbidden');
                            case "error/offline":
                                die('Site Down For Maintenance');
                        }
                    }
                    
                    // Make sure we have a module, controller, and action
                    if(!isset($parts[1]))
                        $parts[1] = ucfirst($Mod->getName());
                    if(!isset($parts[2]))
                        $parts[2] = 'index';
                    
                    $data = array(
                        'controller' => $parts[1],
                        'action' => $parts[2],
                        'params' => array_slice($parts, 3)
                    );
                }
            }
        }
        
        return $Mod;
    }
}

// Init the class
Router::Init();

// EOF