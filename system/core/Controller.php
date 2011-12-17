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
| Class: Controller
| ---------------------------------------------------------------
|
| Main Controller file. This file will act as a base for the
| whole system
|
*/
namespace System\Core;

class Controller
{
    // Our controller name
    public $controller;

    // Our action (sub page)
    public $action;
    
    // Our queryString
    public $querystring;

    // The instance of this class
    private static $instance;


/*
| ---------------------------------------------------------------
| Constructer: __construct()
| ---------------------------------------------------------------
|
| Initiates the self::instance for the ability to use the
| controller as a base for outside files, or.. as codeignitor
| puts it, a "superobject"
|
*/
    public function __construct() 
    {		
        // Set the instance here
        self::$instance = $this;
        
        // Set our Controller and Action
        $this->controller = $GLOBALS['controller'];
        $this->action = $GLOBALS['action'];
        $this->querystring = $GLOBALS['querystring'];
        
        // Initiate the loader
        $this->load = load_class('Loader');
        
        // --------------------------------------
        // Autoload the config autoload_helpers |
        // --------------------------------------
        $libs = config('autoload_helpers', 'Core');
        if(count($libs) > 0)
        {
            foreach($libs as $lib)
            {
                $this->load->helper($lib);
            }
        }
        
        //-----------------------------------------
        // Autoload the config autoload_libraries |
        //-----------------------------------------
        $libs = config('autoload_libraries', 'Core');
        if(count($libs) > 0)
        {
            foreach($libs as $lib)
            {
                $this->load->library($lib);
            }
        }
    }

/*
| ---------------------------------------------------------------
| Function: get_instance()
| ---------------------------------------------------------------
|
| Gateway to adding this controller class to an outside file
|
| @Return: (Object) Returns the instance of this class
|
*/
    public static function get_instance()
    {
        return self::$instance;
    }

/*
| ---------------------------------------------------------------
| Function: _beforeAction()
| ---------------------------------------------------------------
|
| Mini hook of code to be called right before the action
|
*/
    public function _beforeAction() {}

/*
| ---------------------------------------------------------------
| Function: _afterAction()
| ---------------------------------------------------------------
|
| Mini hook of code to be called right after the action
|
*/
    public function _afterAction() {}

}
// EOF