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
        
        // Register the Default Core and Library namespaces with the autoloader
        AutoLoader::Register(); // Register the Autoloader with spl_autoload;
        AutoLoader::RegisterNamespace('Core', path(SYSTEM_PATH, 'core'));
        AutoLoader::RegisterNamespace('Core\IO', path(SYSTEM_PATH, 'core', 'io'));
        AutoLoader::RegisterNamespace('Library', path(SYSTEM_PATH, 'library'));
        AutoLoader::RegisterPath( path(SYSTEM_PATH, 'core', 'exceptions') );
        
        // Init System Benchmark
        Benchmark::Start('System');
        
        // Make sure output buffering is enabled, and started This is pretty important
        ini_set('output_buffering', 'On');
        ob_start();
        
        // Set our exception and error handler, and Accept all errors
        set_exception_handler('Core\ErrorHandler::HandleException');
        set_error_handler('Core\ErrorHandler::HandlePHPError');
        error_reporting(E_ALL);
        
        // Include the Plexis Application
        require path(SYSTEM_PATH, "Plexis.php");
        
        // We are initiated successfully
        self::$isInitiated = true;
        
        try {
            Plexis::Run();
        }
        catch(Exception $e) {
            ErrorHandler::HandleException($e);
        }
    }
}
// EOF