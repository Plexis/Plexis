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
namespace Plugin;

// Bring some classes into scope
use \Core\Config;
use \Core\Database;
use \Core\DatabaseConnectError;
use \Core\Request;
use \Core\Response;
use \Library\Template;

class Plexis
{
    public function __construct()
    {
        // Check for database online, surpress errors
        $DB = false;
        try {
            $DB = Database::Connect('DB', Config::GetVar('PlexisDB', 'DB'));
        }
        catch ( DatabaseConnectError $e ) {}

        // Check if the install directory exists
        $installer = is_dir( ROOT . DS . 'install' );

        // Check if installer files are present
        $locked = file_exists( ROOT . DS . 'install'. DS .'install.lock' );

        // Check if the install folder is still local
        if($DB == false && $locked == false && $installer == true)
        {
            Response::Redirect('install/index.php', 307);
            die;
        }
        elseif($locked == false && $installer == true)
        {
            //Warn that the installer is accessible.
            Template::Message('error', "The installer is publicly accessible! Please rename, delete or re-lock your install folder");
        }
    }
}
?>