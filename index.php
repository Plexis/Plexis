<?php	
/* 
| --------------------------------------------------------------
| 
| Plexis, Professional WoW CMS
|
| --------------------------------------------------------------
|
| Author: 		Steven Wilson
| Copyright:	Copyright (c) 2011, Steven Wilson
| License: 		GNU GPL v3
|
*/

// Define a smaller Directory seperater and ROOT path
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

// Define full paths to the APP and System Folders
define('APP_PATH', ROOT . DS . 'application');
define('SYSTEM_PATH', ROOT . DS . 'system');

/*
| Lets speed to core up by manually loading these system files,
| The Common functions file, and The Registry Class
*/
require (SYSTEM_PATH . DS . 'core' . DS . 'Common.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Registry.php');
 
// Register the Core to process errors with the custom_error_handler method
set_error_handler( 'php_error_handler' , E_ALL | E_STRICT );

// Initiate the system start time
$Benchmark = load_class('Benchmark');
$Benchmark->start('system');

// Initiate the framework and let it do the rest ;)
$Frostbite = load_class('Frostbite');
$Frostbite->Init();
?>