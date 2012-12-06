<?php
/*
| --------------------------------------------------------------
| System Controller
| --------------------------------------------------------------
|
| The system acts as a base, and framework for the applications
|
*/

// First, Import some classes into scope
use Core\AutoLoader;
use Core\Benchmark;
use Core\Config;
use Core\Router;
use Core\ErrorHandler;

class System
{
    private static $isInitiated = false;
    
    public static function Run()
    {
        // Dont allow the system to run twice
        if(self::$isInitiated) return;
        
        // Define namespace for autloader
        AutoLoader::RegisterNamespace('Core', path(SYSTEM_PATH, 'core'));
        
        // Init System Benchmark //
        Benchmark::Start('system');
        
        // Make sure output buffering is enabled. This is pretty important
        ini_set('output_buffering', 'On');
        
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

class SystemError extends Exception {}