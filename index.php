<?php
/*
| --------------------------------------------------------------
| Plexis Core, Multiple Application Platform
| --------------------------------------------------------------
|
| Author:       Steven (Wilson212)
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/

// Make sure we are running php version 5.3.0 or newer!!!!
if(!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50302)
    die('PHP version 5.3.2 or newer required to run Plexis Core. Your version: '. PHP_VERSION);
    
// Get a most accurate start time
define('TIME_START', microtime(true));
    
// Define a smaller Directory seperater and ROOT, SYSTEM paths
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
define('SYSTEM_PATH', ROOT . DS .'system');

// Point php to our own php error log
ini_set('error_log', SYSTEM_PATH . DS .'logs'. DS .'php_errors.log');
	
// Include the required script to run the system
require SYSTEM_PATH . DS .'core'. DS .'Common.php';
require SYSTEM_PATH . DS .'core'. DS .'AutoLoader.php';
require SYSTEM_PATH . DS .'System.php';

// Run the system. Application will be run on success
System::Run();