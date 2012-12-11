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
     * The requested cotnroller name
     * @var string
     */
    protected static $controller;

    /**
     * The requested method name
     * @var string
     */
    protected static $action;
    
    /**
     * The request uri
     * @var string
     */
    protected static $uri;
    
    /**
     * The querystring parameters
     * @var string[]
     */
    protected static $params;
    
    /**
     * Returns all the url information
     *
     * @return string[] Returns array('controller' => , 'action' => , 'params' => );
     */
    public static function GetRequest()
    {
        return array(
            'controller' => self::$controller,
            'action' => self::$action,
            'params' => self::$params
        );
    }
    
    /**
     * Returns the controller name from the URI
     *
     * @return string
     */
    public static function GetController()
    {
        return self::$controller;
    }
    
    /**
     * Returns the method name from the URI
     *
     * @return string
     */
    public static function GetAction()
    {
        return self::$action;
    }
    
    /**
     * Returns the action parameters from the URI
     *
     * @return string[]
     */
    public static function GetParams()
    {
        return self::$params;
    }
    
    /**
     * Returns the specified URI segement
     *
     * @param int $index The uri segement index
     * @return string|bool Returns false if the URI index is out of range
     */
    public static function GetUriSegement($index)
    {
        // Make sure we've at least routed the url here;
        if(!self::$routed) self::RouteUrl();
        
        return (isset(self::$uri[$index])) ? self::$uri[$index] : false;
    }
    
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
        
        // Add trace for debugging
        // \Debug::trace('Routing url...', __FILE__, __LINE__);

        // Process the site URI
        if( !Config::GetVar('enable_query_strings', 'Plexis'))
        {
            // Get our current url, which is passed on by the 'url' param
            self::$uri = (isset($_GET['uri'])) ? Security::Clean(Request::Query('uri')) : '';   
        }
        else
        {
            // Define our needed vars
            $c_param = Config::GetVar('controller_param', 'Plexis');
            $a_param = Config::GetVar('action_param', 'Plexis');
            
            // Make sure we have a controller at least
            $c = Security::Clean(Request::Query($c_param));
            if( !$c )
            {
                self::$uri = '';
            }
            else
            {
                // Get our action
                $a = Security::Clean(Request::Query($a_param));
                if( !$a ) $a = Config::GetVar('default_action', 'Plexis'); // Default Action
                
                // Init the uri
                self::$uri = $c .'/'. $a;
                
                // Clean the query string
                $qs = Security::Clean( $_SERVER['QUERY_STRING'] );
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
            $controller = Config::GetVar('default_controller', 'Plexis'); // Default Controller
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
            $controller = $urlArray[0];
            
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
        
        // Make sure the first character of the controller is not an _ !
        if( strncmp($controller, '_', 1) == 0 || strncmp($action, '_', 1) == 0 )
        {
            // Add this to the trace
            // \Debug::trace('Controller or action contains a private prefix "_", showing 404' , __FILE__, __LINE__);
            \Plexis::Show404();
        }
        
        // Set static Variables
        self::$controller = $controller;
        self::$action = $action;
        self::$params = $params;
        
        // Add trace for debugging
        // \Debug::trace("Url routed successfully. Found controller: ". self::$controller ."; Action: ". self::$action ."; Querystring: ". implode('/', self::$params), __FILE__, __LINE__);
    }
}

// Init the class
Router::RouteUrl();

// EOF