<?php
/*
| --------------------------------------------------------------
| Application Backend Controller
| --------------------------------------------------------------
|
| The backend controller is the main method for running the 
| Application
|
*/

// First, import some classes into scope
use Core\AutoLoader;
use Core\Benchmark;
use Core\Config;
use Core\Database;
use Core\DatabaseConnectError;
use Core\Router;
use Library\Template;
use Library\View;
use Library\ViewNotFoundException;

class Plexis
{
    private static $isRunning = false;
    public static $modulePath;
    public static $module;
    public static $action;
    
    public static function Run()
    {
        // Make sure only one instance of the cms is running at a time
        if(self::$isRunning) return;
        
        // We are now running
        self::$isRunning = true;
        
        // Tell the autoloader something
        AutoLoader::RegisterNamespace('Library', path( ROOT, "plexis", "library" ));
        
        // Import the constants file
        require path(ROOT, "plexis", "constants.php");
        
        // Just a default output for now
        echo "<center>New Plexis Version: ". CMS_MAJOR_VER .".". CMS_MINOR_VER .".". CMS_MINOR_REV;
        
        // Load the Plexis Config file
        $file = path(ROOT, "plexis", "config", "config.php");
        Config::Load($file, 'Plexis');
        
        // Load Database config file
        $file = path(ROOT, "plexis", "config", "database.config.php");
        Config::Load($file, 'DB', 'DB_Configs');
        
        // Test database connection
        $message = null;
        try {
            // Database::Connect('DB', Config::GetVar('PlexisDB', 'DB'));
            $message = 'Database connection successful!';
        }
        catch( DatabaseConnectError $e ) {
            $message = $e->getMessage();
        }
        
        // Load Auth class and User
        
        // Set default theme path
        Template::SetThemePath( path(ROOT, "plexis", "third_party", "themes", "default") );
        
        // Load our controller etc etc
        self::ProccessRoute();
        
        // Show our elapsed time
        Template::Render();
        echo "<br /><br /><small>Page Loaded In: ". Benchmark::ElapsedTime('total_script_exec', 5);
    }
    
    public static function LoadModule($name, $args = array())
    {
        // HMVC method to run other modules internally, and capture the output
    }
    
    protected static function ProccessRoute()
    {
        // Get URL info
        $url = Router::GetUrlInfo();
        
        // For now, just load the default controller
        $controller = Config::GetVar('default_controller', 'Plexis');
        
        // Define out module path, and our module controller path
        self::$modulePath = path( ROOT, 'plexis', 'components', strtolower($controller) );
        $path = path(self::$modulePath, 'controllers', $controller .'.php');
        
        // Require controller class
        require $path;
        
        // Define our controller and index globablly
        self::$module = $controller;
        self::$action = $action = 'Index';
        
        // Init the new controller
        $controller = new $controller();
        $controller->{$action}();
    }
}

// Any and all exceptions thrown from the Application should extend the ApplicationError class
class ApplicationError extends Exception {}