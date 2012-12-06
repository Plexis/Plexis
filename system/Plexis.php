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
use Core\Dispatch;
use Core\Response;
use Core\Router;
use Library\Template;

class Plexis
{
    private static $isRunning = false;
    public static $modulePath;
    public static $module;
    public static $action;
    
    // Option variables for controllers
    protected static $renderTemplate = true;
    
/*
| ---------------------------------------------------------------
| Method: Run()
| ---------------------------------------------------------------
|
| Main method for running the Plexis application
|
*/ 
    public static function Run()
    {
        // Make sure only one instance of the cms is running at a time
        if(self::$isRunning) return;
        
        // We are now running
        self::$isRunning = true;
        
        // Tell the autoloader something
        AutoLoader::RegisterNamespace('Library', path( SYSTEM_PATH, "library" ));
        
        // Import the constants file
        require path(SYSTEM_PATH, "constants.php");
        
        // Just a default output for now
        echo "<center>New Plexis Version: ". CMS_MAJOR_VER .".". CMS_MINOR_VER .".". CMS_MINOR_REV;
        
        // Load the Plexis Config file
        $file = path(SYSTEM_PATH, "config", "config.php");
        Config::Load($file, 'Plexis');
        
        // Load Database config file
        $file = path(SYSTEM_PATH, "config", "database.config.php");
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
        Template::SetThemePath( path(ROOT, "third_party", "themes", "default") );
        
        // Load our controller etc etc
        self::RunModule();
        
        // Do we render the template?
        if(self::$renderTemplate)
            Template::Render();
        
        // Show our elapsed time (testing purposes)
        echo "<br /><br /><small>Page Loaded In: ". Benchmark::ElapsedTime('total_script_exec', 5);
        
        //Send the response to the browser
        Response::Send();
    }
    
/*
| ---------------------------------------------------------------
| Method: RenderTemplate()
| ---------------------------------------------------------------
|
| Sets whether plexis should render the full template or not
|
*/
    public static function RenderTemplate($bool = true)
    {
        if(!is_bool($bool)) return;
        
        self::$renderTemplate = $bool;
    }
    
/*
| ---------------------------------------------------------------
| Method: RunModule()
| ---------------------------------------------------------------
|
| Internal method for running the controller and action
|
*/
    protected static function RunModule()
    {
        // Get URL info
        $Request = Router::GetRequest();
        
        // For now, just load the default controller
        $controller = ucfirst(Config::GetVar('default_controller', 'Plexis'));
        
        // Define our controller and index globablly
        self::$module = $controller;
        self::$action = $action = 'Index';
        
        // Define out module path, and our module controller path
        self::$modulePath = path( SYSTEM_PATH, 'modules', strtolower($controller) );
        
        // Setup the dispatch, and run the module
        Dispatch::SetControllerPath( path(self::$modulePath, 'controllers') );
        Dispatch::SetController('frontpage');
        Dispatch::Execute();
    }
}

// Any and all exceptions thrown from the Application should extend the ApplicationError class
class ApplicationError extends Exception {}