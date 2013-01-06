<?php
/**
 * Plexis Content Management System
 *
 * @author      Steven Wilson (Wilson212)
 * @author      Tony (Syke)
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @package     System
 */

// Make sure we are running php version 5.3.2 or newer!!!!
if(!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50302)
    die('PHP version 5.3.2 or newer required to run Plexis. Your version: '. PHP_VERSION);
    
/** Most Accurate Start time */
define('TIME_START', microtime(true));
    
/** Directory Separator */
define('DS', DIRECTORY_SEPARATOR);

/** Root Path to The cms */
define('ROOT', dirname(__FILE__));

/** Root path to the System folder */
define('SYSTEM_PATH', ROOT . DS .'system');

/** Define if we are running a mod rewrite enviroment */
define('MOD_REWRITE', isset($_SERVER["HTTP_MOD_REWRITE"]) && $_SERVER["HTTP_MOD_REWRITE"] == "On");

// Point php to our own php error log
ini_set('error_log', SYSTEM_PATH . DS .'logs'. DS .'php_errors.log');
    
// Include the required script to run the system
require SYSTEM_PATH . DS .'core'. DS .'AutoLoader.php';
require SYSTEM_PATH . DS .'helpers'. DS .'Io.php';
require SYSTEM_PATH . DS .'System.php';

// Run the system. Application will be run on success
System::Run();