<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
| ---------------------------------------------------------------
| Class: Router
| ---------------------------------------------------------------
|
| This class is used to determine our controller / action. It is
| also used for module checking, or spitting out 404's
|
*/
namespace System\Core;

class Router
{
    // Our http protocol (https or http)
    protected $protocol;
    
    // Our hostname
    protected $http_host;
    
    // Our Site URL
    protected $site_url;
    
    // The requested URI
    protected $uri;
    
    // Our site directory
    protected $site_dir;
    
    // Our controller name
    protected $controler;

    // Our action (sub page)
    protected $action;

    // The querystring
    protected $queryString;

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
    public function route_url() 
    {
        // Determine our http hostname, and site directory
        $this->http_host = rtrim($_SERVER['HTTP_HOST'], '/');
        $this->site_dir = dirname( $_SERVER['PHP_SELF'] );
        
        // Detect our protocol
        if(isset($_SERVER['HTTPS']))
        {
            if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
            {
                $this->protocol = 'https';
            }
            else
            {
                $this->protocol = 'http';
            }
        }
        else
        {
            $this->protocol = 'http';
        }
        
        // Build our Full URL
        $site_url = str_replace('//', '/', $this->http_host .'/'. $this->site_dir);
        $this->site_url = $this->protocol .'://' . $site_url;

        // Get our current url, which is passed on by the htaccess file
        $this->uri = (isset($_GET['url']) ? $_GET['url'] : '');

        // If the URI is empty, then load defaults
        if(empty($this->uri)) 
        {
            // Set our Controller / Action to the defaults
            $controller = config('default_controller', 'Core'); // Default Controller
            $action = config('default_action', 'Core'); // Default Action
            $queryString = array(); // Default query string
        }
        
        // There is a URI, Lets load our controller and action
        else 
        {
            // We will start by bulding our controller, action, and querystring
            $urlArray = array();
            $urlArray = explode("/", $this->uri);
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
                $action = config('default_action', 'Core'); // Default Action
            }
            
            // $queryString is what remains
            $queryString = $urlArray;
        }
        
        // Make sure the first character of the controller is not an _ !
        if( strncmp($controller, '_', 1) == 0 || strncmp($action, '_', 1) == 0 )
        {
            show_404();
        }
        
        // Set static Variables
        $this->controller = $controller;
        $this->action = $action;
        $this->queryString = $queryString;
        
        return $this->get_url_info();
    }

/*
| ---------------------------------------------------------------
| Method: get_url_info()
| ---------------------------------------------------------------
|
| This method returns all the url information
|
| @Return (Array) Returns an array of all url related info
|
*/    
    public function get_url_info()
    {
        $array = array(
            'protocol' => $this->protocol,
            'http_host' => $this->http_host,
            'site_url' => $this->site_url,
            'site_dir' => $this->site_dir,
            'uri' => $this->uri,
            'controller' => $this->controller,
            'action' => $this->action,
            'querystring' => $this->queryString
        );
        return $array;
    }
}
// EOF