<?php
/**
 * Plexis Content Management System
 *
 * @file        System/System.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 */

// First, Import some classes into scope
use Core\AutoLoader;
use Core\Benchmark;
use Core\Config;
use Core\Router;
use Core\ErrorHandler;

/**
 * The system acts as a  wrapper for plexis. It catches un-caught 
 * exceptions, and sets up a base for plexis to work on
 *
 * @author      Steven Wilson 
 * @package     System
 */
class System
{
    /**
     * Internal var that prevents the system from running twice
     * @var bool
     */
    private static $isInitiated = false;
    
    /**
     * Initiates the System wrapper for plexis
     *
     * @return void
     */
    public static function Run()
    {
        // Dont allow the system to run twice
        if(self::$isInitiated) return;
        
        // Register the Core and Library namespaces with the autoloader
        AutoLoader::RegisterNamespace('Core', path(SYSTEM_PATH, 'core'));
        AutoLoader::RegisterNamespace('Library', path( SYSTEM_PATH, "library" ));
        
        // Init System Benchmark
        Benchmark::Start('system');
        
        // Make sure output buffering is enabled. This is pretty important
        ini_set('output_buffering', 'On');
        ob_start();
        
        // Set our exception and error handler
        set_exception_handler('Core\ErrorHandler::HandleException');
        set_error_handler('Core\ErrorHandler::HandlePHPError');
        
        // We are initiated successfully
        self::$isInitiated = true;
        
        // Run the application
        require path(SYSTEM_PATH, "Plexis.php");
        
        try {
            Plexis::Run();
        }
        catch(Exception $e) {
            ErrorHandler::HandleException($e);
        }
    }
}

/**
 * System Error exception
 * @package     System
 * @subpackage  Exceptions
 * @file        System/System.php
 */
class SystemError extends Exception {}