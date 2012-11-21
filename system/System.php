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
        
        // Load system configs
        Config::Load( path(SYSTEM_PATH, 'config', 'config.php'), 'System', 'config', true, false );
		
        // We are initiated successfully
		self::$isInitiated = true;
		
		// Run the application
		require path(ROOT, "plexis", "Plexis.php");
		
		try {
			Plexis::Run();
		}
        catch(ApplicationError $e) {
            ErrorHandler::HandleException($e);
        }
		catch(Exception $e) {
			ErrorHandler::HandlePHPException($e);
		}
	}
    
    public static function TriggerError($lvl, $message, $file, $line) 
    {
        ErrorHandler::TriggerError($lvl, $message, $file, $line);
    }
}

class SystemError extends Exception {}