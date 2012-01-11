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
namespace Application\Core;

class Frostbite
{
    public $Router;
    protected $dispatch;
    protected $controller;
    protected $action;

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
        $controller = $routes['controller'];
        $action     = $routes['action'];
        $queryString  = $GLOBALS['querystring']  = $routes['querystring'];
        
        // Define our site url
        define('SITE_URL', $routes['site_url']);

        // -----------------------------------------
        // Lets include the application controller.|
        // -----------------------------------------		
        if( !$this->loadApplication($controller, $action) )
        {
            show_404();
        }
        
        // -------------------------------------------------------------
        // Here we init the actual controller / action into a variable.|
        // -------------------------------------------------------------
        $this->dispatch = new $this->controller();
        
        // After loading the controller, make sure the method exists, or we have a 404
        if(method_exists($this->controller, $this->action)) 
        {
            // -------------------------------------------------------------------------
            // Here we call the contoller's before, requested, and after action methods.|
            // -------------------------------------------------------------------------
        
            // Call the beforeAction method in the controller.
            $this->performAction($this->controller, "_beforeAction", $queryString);
            
            // HERE is where the magic begins... call the Main APP Controller and method
            $this->performAction($this->controller, $this->action, $queryString);
            
            // Call the afterAction method in the controller.
            $this->performAction($this->controller, "_afterAction", $queryString);

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

/*
| ---------------------------------------------------------------
| Method: loadApplication()
| ---------------------------------------------------------------
|
| Checks the controller and Module folders for a the controller
| and then loads them
|
| @Return: (Bool) - If the controller exists, it returns TRUE
|
*/
    protected function loadApplication($controller, $action)
    {
        // Make this a bit easier
        $name = strtolower($controller);
        
        // Load the loader class and DB connection
        $Load = load_class('Loader');
        $DB = $Load->database('DB');
        
        // Build our array to get out current URI's module if one exists
        $query = "SELECT * FROM `pcms_modules` WHERE `uri`=?";
        $uri = $name .'/'. $action;
        $result = $DB->query( $query, array($uri) )->fetch_row();
        
        // If our result is an array, we have a result
        if(is_array($result))
        {
            // Define out globals and this controller/action
            $GLOBALS['is_module'] = TRUE;
            $this->controller  = $GLOBALS['controller'] = ucfirst($result['name']);
            $this->action = $GLOBALS['action'] = $result['method'];
            
            // Include the module controller file
            include (APP_PATH . DS . 'modules' . DS . $name . DS . 'controller.php');
            return TRUE;
        }
        
        // Check the App controllers folder
        elseif(file_exists(APP_PATH . DS . 'controllers' . DS . $name . '.php')) 
        {
            // Define out globals and this controller/action
            $GLOBALS['is_module'] = FALSE;
            $this->controller  = $GLOBALS['controller'] = $controller;
            $this->action = $GLOBALS['action'] = $action;
            
            // Include the controller file
            include (APP_PATH . DS . 'controllers' . DS . $name . '.php');
            return TRUE;
        }
        
        // Neither exists, then no controller found.
        return FALSE;
    }
}
// EOF