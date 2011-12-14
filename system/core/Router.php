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
    // Our controller name
    protected $controler;

    // Our action (sub page)
    protected $action;

    // The querystring
    protected $queryString;

/*
| ---------------------------------------------------------------
| Method: routeUrl()
| ---------------------------------------------------------------
|
| This method analyzes the url to determine the controller / action
| and query string
|
| @Return (Array) Returns an array of controller, action and queryString
|
*/
    public function routeUrl() 
    {
        // Include our routes config
        include APP_PATH . DS . 'config' . DS . 'routes.php';
        
        // Get our current url, which is passed on by the htaccess file
        $url = (isset($_GET['url']) ? $_GET['url'] : '');

        // If the URI is empty, then load defaults
        if(empty($url)) 
        {
            $controller = $routes['default_controller']; // Default Controller
            $action = $routes['default_action']; // Default Action
            $queryString = array(); // Default query string
        }
        
        // There is a URI, Lets load our controller and action
        else 
        {
            $urlArray = array();
            $urlArray = explode("/",$url);
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
                $action = $routes['default_action']; // Default Action
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
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_queryString = $queryString;
        
        return array('controller' => $controller, 'action' => $action, 'queryString' => $queryString);
    }
}
// EOF