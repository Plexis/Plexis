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
define('CMS_BUILD', 256);
define('REQ_DB_VERSION', '0.17');

// Define a smaller Directory seperater and ROOT path
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

// Define full paths to the System Folder
define('SYSTEM_PATH', ROOT . DS . 'system');

// Include required scripts to run the system
require (SYSTEM_PATH . DS . 'core' . DS . 'Common.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Registry.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Debug.php');

// Initiate the system start time
load_class('Benchmark')->start('system');

// Initiate the framework and let it do the rest ;)
load_class('Plexis')->Init();
?>