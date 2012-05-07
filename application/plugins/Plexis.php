<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Plugin: Plexis
| ---------------------------------------------------------------
|
| Main plugin for detecting whether the system needs installed
| Displays error message when install folder exists, but the system
| is installed
|
*/
namespace Application\Plugins;

class Plexis
{
    public function pre_system()
    {
        // Check for database online, surpress errors
        $DB = load_class('Loader')->database('DB', false, true);
        
        // Check if installer files are present
        $installer = file_exists( ROOT . DS . 'install/index.php' );
        
        // Check if the install folder is still local
        if($DB == false && $installer == true)
        {
            redirect('install/index.php');
            die();
        }
        else
        {
            // Set a nice output message for the admin telling him to delete the installer folder!
            $GLOBALS['template_messages'][] = '<div class="alert error">Install folder still exists! Please Rename or delete your install folder.</div>';
        }
    }
    
    // Un used by this plugin, but they HAVE TO BE defined!
    public function pre_controller() {}
    public function post_controller_constructor() {}
    public function post_controller() {}
}