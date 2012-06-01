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
namespace Plugins;

class Plexis
{
    public function pre_system()
    {
        // Check for database online, surpress errors
        $DB = load_class('Loader')->database('DB', false, true);
        
        // Check if the install directory exists
        $installer = is_dir( ROOT . DS . 'install' );
        
        // Check if installer files are present
        $locked = file_exists( ROOT . DS . 'install'. DS .'install.lock' );
        
        // Check if the install folder is still local
        if($DB == false && $locked == false && $installer == true)
        {
            header('Location: '. SITE_URL .'/install/index.php');
            die();
        }
		elseif($locked == false && $installer == true)
		{
			//Warn that the installer is accessible.
			$GLOBALS["template_messages"][] = "<div class=\"alert error\">The installer is publicly accessible! Please rename, delete or re-lock your install folder.</div>";
		}
    }
    
    // Un used by this plugin, but they HAVE TO BE defined!
    public function pre_controller() {}
    public function post_controller_constructor() {}
    public function post_controller() {}
}

?>