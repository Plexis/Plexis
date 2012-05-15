<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis
| License:      GNU GPL v3
|
| * USE THIS FILE AS THE BOOTSTRAP *
|
*/
namespace Application\Core;

class Frostbite
{
    protected $Router;
    protected $load;
    protected $DB;
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
        $this->load = load_class('Loader');
        
        // Tell the router to process the URL for us
        $routes = $this->Router->get_url_info();
        
        // Initialize some important routing variables
        $controller = $routes['controller'];
        $action     = $routes['action'];
        $queryString  = $GLOBALS['querystring']  = $routes['querystring'];
        
        // Define our Base url
        define('BASE_URL', $routes['site_url']);
        
        // Define our site url
        if( !array_key_exists('HTTP_MOD_REWRITE', $_SERVER) ) $_SERVER['HTTP_MOD_REWRITE'] == 'Off';
        ($_SERVER['HTTP_MOD_REWRITE'] == 'On') ? define('SITE_URL', $routes['site_url']) : define('SITE_URL', $routes['site_url'] . '/?url=');
        
        // -----------------------------------------
        // Load Plugins                            |
        // -----------------------------------------
        include( APP_PATH . DS .'config'. DS .'plugins.php' );
        
        // Load pre system plugins
        foreach($Plugins as $p)
        {
            $this->load->plugin($p, 'pre_system');
        }

        // -----------------------------------------
        // Lets include the application controller.|
        // -----------------------------------------		
        if( !$this->loadApplication($controller, $action) )
        {
            show_404();
        }
        
        // Prcoess Pre controller Plugins
        foreach($Plugins as $p)
        {
            $this->load->plugin($p, 'pre_controller');
        }
        
        // -------------------------------------------------------------
        // Here we init the actual controller / action into a variable.|
        // -------------------------------------------------------------
        $this->dispatch = new $this->controller();
        
        // Prcoess Post controller construct Plugins
        foreach($Plugins as $p)
        {
            $this->load->plugin($p, 'post_controller_constructor');
        }
        
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
        
        // Prcoess Post controller construct Plugins
        foreach($Plugins as $p)
        {
            $this->load->plugin($p, 'post_controller');
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
        
        // Load database
        $this->DB = $this->load->database('DB');
        
        // Build our array to get out current URI's module if one exists
        $uri1 = $name .'/*';
        $uri2 = $name .'/'. $action;
        
        // Check to see if the URI belongs to a module
        $query = "SELECT * FROM `pcms_modules` WHERE `uri`=? OR `uri`=?";
        $result = $this->DB->query( $query, array($uri1, $uri2) )->fetch_row();
        
        // If our result is an array, Then we load it as a module
        if(is_array($result))
        {
            // Handle the method, if method is astricks, then the module will handle all requests
            if($result['method'] == '*')
            {
                $result['method'] = $action;
            }
            
            // If the method is an array (imploded with a comma), we see if the action is in the array
            elseif(strpos($result['method'], ',') !== FALSE)
            {
                // Remove any spaces, and convert to an array
                $uri = str_replace(" ", "", $result['method']);
                $array = explode(',', $uri);
                if(!in_array($action, $array))
                {
                    // The action IS NOT in the array, load default controller
                    goto Skip;
                }
                else
                {
                    // The action is in the array, so we set it as that
                    $result['method'] = $action;
                }
            }

            // Define out globals and this controller/action
            $GLOBALS['is_module'] = TRUE;
            $this->controller  = $GLOBALS['controller'] = ucfirst($result['name']);
            $this->action = $GLOBALS['action'] = $result['method'];
            
            // Include the module controller file
            include (APP_PATH . DS . 'modules' . DS . $result['name'] . DS . 'controller.php');
            return TRUE;
        }
        
        // Check the App controllers folder
        else 
        {
            Skip:
            {
                if(file_exists(APP_PATH . DS . 'controllers' . DS . $name . '.php'))
                {
                    // Define out globals and this controller/action
                    $GLOBALS['is_module'] = FALSE;
                    $this->controller  = $GLOBALS['controller'] = $controller;
                    $this->action = $GLOBALS['action'] = $action;
                    
                    // Include the controller file
                    include (APP_PATH . DS . 'controllers' . DS . $name . '.php');
                    return TRUE;
                }
            }
        }
        
        // Neither exists, then no controller found.
        return FALSE;
    }
}
// EOF