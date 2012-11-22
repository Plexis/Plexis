<?php
/* 
| --------------------------------------------------------------
| Plexis Core
| --------------------------------------------------------------
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Router
| ---------------------------------------------------------------
|
| This class is used to determine our controller / action. It is
| also used for module checking, or spitting out 404's
|
*/
namespace Core;

class Router
{
    // Have we routed the url yet?
    public static $routed = false;
    
    // Our http protocol (https or http)
    protected static $protocol;
    
    // Our hostname
    protected static $http_host;
    
    // Our Site URL
    protected static $site_url;
    
    // The requested URI
    protected static $uri;
    
    // Our site directory
    protected static $site_dir;
    
    // Our controller name
    protected static $controller;

    // Our action (sub page)
    protected static $action;

    // The querystring
    protected static $queryString;
    
/*
| ---------------------------------------------------------------
| Method: GetUrlInfo()
| ---------------------------------------------------------------
|
| This method returns all the url information
|
| @Return (Array) Returns an array of all url related info
|
*/    
    public static function GetUrlInfo()
    {
        // Make sure we've at least routed the url here;
        if(!self::$routed) self::RouteUrl();
        
        return array(
            'protocol' => self::$protocol,
            'http_host' => self::$http_host,
            'site_url' => self::$site_url,
            'site_dir' => self::$site_dir,
            'uri' => self::$uri,
            'controller' => self::$controller,
            'action' => self::$action,
            'querystring' => self::$queryString
        );
    }
    
/*
| ---------------------------------------------------------------
| Method: GetUriSegement()
| ---------------------------------------------------------------
|
| This method returns the specified URI segement
|
| @Return (String) Returns the segement, or false
|
*/    
    public static function GetUriSegement($index)
    {
        // Make sure we've at least routed the url here;
        if(!self::$routed) self::RouteUrl();
        
        return (isset(self::$uri[$index])) ? self::$uri[$index] : false;
    }

/*
| ---------------------------------------------------------------
| Method: route_url()
| ---------------------------------------------------------------
|
| This method analyzes the url to determine the controller / action
| and query string
|
| @Return (Array) Returns an array of controller, action and queryString
|
*/
    protected static function RouteUrl() 
    {
        // Make sure we only route once
        if(self::$routed) return;
        
        // Add trace for debugging
        // \Debug::trace('Routing url...', __FILE__, __LINE__);
        
        // Determine our http hostname, and site directory
        self::$http_host = rtrim($_SERVER['HTTP_HOST'], '/');
        self::$site_dir = dirname( $_SERVER['PHP_SELF'] );
        
        // Detect our protocol
        if(isset($_SERVER['HTTPS']))
        {
            self::$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
        }
        else
        {
            self::$protocol = 'http';
        }
        
        // Build our Full Base URL
        $site_url = self::$http_host .'/'. self::$site_dir;
        while(strpos($site_url, '//') !== false) $site_url = str_replace('//', '/', $site_url);
        self::$site_url = str_replace( '\\', '', self::$protocol .'://' . rtrim($site_url, '/') );

        // Process the site URI
        if( !Config::GetVar('enable_query_strings', 'System'))
        {
            // Get our current url, which is passed on by the 'url' param
            self::$uri = (isset($_GET['uri'])) ? Input::Get('uri', true) : '';   
        }
        else
        {
            // Define our needed vars
            $c_param = Config::GetVar('controller_param', 'System');
            $a_param = Config::GetVar('action_param', 'System');
            
            // Make sure we have a controller at least
            $c = Input::Get($c_param, true );
            if( !$c )
            {
                self::$uri = '';
            }
            else
            {
                // Get our action
                $a = Input::Get( $a_param, true );
                if( !$a ) $a = Config::GetVar('default_action', 'System'); // Default Action
                
                // Init the uri
                self::$uri = $c .'/'. $a;
                
                // Clean the query string
                $qs = Input::Clean( $_SERVER['QUERY_STRING'] );
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
            $controller = Config::GetVar('default_controller', 'System'); // Default Controller
            $action = Config::GetVar('default_action', 'System'); // Default Action
            $queryString = array(); // Default query string
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
                $action = Config::GetVar('default_action', 'System'); // Default Action
            }
            
            // $queryString is what remains
            $queryString = $urlArray;
        }
        
        // Tell the system we've routed
        self::$routed = true;  
        
        // Make sure the first character of the controller is not an _ !
        if( strncmp($controller, '_', 1) == 0 || strncmp($action, '_', 1) == 0 )
        {
            // Add this to the trace
            // \Debug::trace('Controller or action contains a private prefix "_", showing 404' , __FILE__, __LINE__);
            show_404();
        }
        
        // Set static Variables
        self::$controller = $controller;
        self::$action = $action;
        self::$queryString = $queryString;
        
        // Add trace for debugging
        // \Debug::trace("Url routed successfully. Found controller: ". self::$controller ."; Action: ". self::$action ."; Querystring: ". implode('/', self::$queryString), __FILE__, __LINE__);
    }
}
// EOF