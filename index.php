<?php	
/* 
| --------------------------------------------------------------
| 
| Plexis CMS
|
| --------------------------------------------------------------
|
| Author:       Steven (Wilson212)
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/

// Define CMS versions
define('CMS_VERSION', 'Beta 1');
define('CMS_BUILD', 329);
define('REQ_DB_VERSION', '0.21');

// Define a smaller Directory seperater and ROOT, SYSTEM paths
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
define('SYSTEM_PATH', ROOT . DS .'system');

// Point php to our own php error log
ini_set('error_log', SYSTEM_PATH . DS .'logs'. DS .'php_errors.log');

// Make sure we are running php version 5.3.0 or newer!!!!
if(!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300):
    die('PHP version 5.3.0 or newer required to run Plexis cms. Your version: '. PHP_VERSION);
endif;

// Include required scripts to run the system
require (SYSTEM_PATH . DS .'core'. DS .'Common.php');
require (SYSTEM_PATH . DS .'core'. DS .'Registry.php');
require (SYSTEM_PATH . DS .'core'. DS .'Debug.php');

// Initiate the system start time
load_class('Benchmark')->start('system');

// Initiate the framework and let it do the rest ;)
load_class('Plexis')->Init();
?>