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
| * USE THIS FILE AS THE BOOTSTRAP *
|
*/
namespace System\Core;

class Frostbite
{
    protected $Router;
    protected $dispatch;

/*
| ---------------------------------------------------------------
| Method: Init()
| ---------------------------------------------------------------
|
| This is the function that runs the whole show!
|
| @Return: (None)
|
*/
    public function Init()
    {
        // Initialize the router
        $this->Router = load_class('Router');
        
        // Tell the router to process the URL for us
        $routes = $this->Router->get_url_info();
        
        // Initialize some important routing variables
        $controller   = $GLOBALS['controller']   = $routes['controller'];
        $action       = $GLOBALS['action']       = $routes['action'];
        $queryString  = $GLOBALS['querystring']  = $routes['querystring'];

        // -----------------------------------------
        // Lets include the application controller.|
        // -----------------------------------------		
        if(file_exists(APP_PATH . DS . 'controllers' . DS . strtolower($controller) . '.php')) 
        {
            include (APP_PATH . DS . 'controllers' . DS . strtolower($controller) . '.php');
        }
        else
        {
            show_404();
        }
        
        // -------------------------------------------------------------
        // Here we init the actual controller / action into a variable.|
        // -------------------------------------------------------------
        $this->dispatch = new $controller();
        
        // After loading the controller, make sure the method exists, or we have a 404
        if(method_exists($controller, $action)) 
        {
            // -------------------------------------------------------------------------
            // Here we call the contoller's before, requested, and after action methods.|
            // -------------------------------------------------------------------------
        
            // Call the beforeAction method in the controller.
            $this->performAction($controller, "_beforeAction", $queryString);
            
            // HERE is where the magic begins... call the Main APP Controller and method
            $this->performAction($controller, $action, $queryString);
            
            // Call the afterAction method in the controller.
            $this->performAction($controller, "_afterAction", $queryString);

        } 
        else 
        {
            // If the method didnt exist, then we have a 404
            show_404();
        }
    }

/*
| ---------------------------------------------------------------
| Method: performAction()
| ---------------------------------------------------------------
|
| @Param: (String) $controller - Name of the controller being used
| @Param: (String) $action - Action method being used in the controller
| @Param: (String) $queryString - The query string, basically params for the Action
| @Return: (Object) - Returns the method
|
*/

    protected function performAction($controller, $action, $queryString = null) 
    {
        if(method_exists($controller, $action)) 
        {
            return call_user_func_array( array($this->dispatch, $action), $queryString );
        }
        return FALSE;
    }
}
// EOF