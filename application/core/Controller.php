<?php
/* 
| --------------------------------------------------------------
| 
| Plexis CMS
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
namespace Application\Core;

class Controller extends \System\Core\Controller
{

/*
| ---------------------------------------------------------------
| Constructer: __construct()
| ---------------------------------------------------------------
|
*/
    public function __construct() 
    {
        // Check if the install folder is still local
        if(file_exists( ROOT . DS . 'install/index.php'))
        {
            redirect('install/index.php');
            die();
        }
        
        // Build the Core Controller
        parent::__construct();
        
        // load module config file if there is one
        if($GLOBALS['is_module'] == TRUE)
        {
            load_module_config($GLOBALS['controller']);
        }
        
        // Setup our template system
        if($this->controller == 'admin')
        {
            $this->Template->set_template('admin');
        }
        else
        {
            // Check if the user has a selected theme.
            $user = $this->Session->data['user'];
            if($user['logged_in'] == FALSE)
            {
                $this->Template->set_template('site', config('default_template'));
            }
            else
            {
                (!empty($user['selected_theme'])) ? $theme = $user['selected_theme'] : $theme = config('default_template');
                $this->Template->set_template('site', $theme);
            }
        }
    }
}
// EOF