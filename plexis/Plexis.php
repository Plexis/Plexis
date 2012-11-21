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
        $url = Router::GetUrlInfo();
        echo "<center>New Plexis Version: ". CMS_MAJOR_VER .".". CMS_MINOR_VER .".". CMS_MINOR_REV;
        echo "<br />Url: ", $url['site_url'];
        
        // More stuff
        $file = path(ROOT, "plexis", "config", "database.config.php");
        Config::Load($file, 'DB', 'DB_Configs');
        
        // Test database connection
        $message = null;
        try {
            Database::Connect('DB', Config::GetVar('PlexisDB', 'DB'));
            $message = 'Database connection successful!';
        }
        catch( DatabaseConnectError $e ) {
            $message = $e->getMessage();
        }
        
        // Load our view
        try {
            $view = new View( path(ROOT, "plexis", "TestView.tpl") );
            $view->Set('message', $message);
            Template::Add($view);
        }
        catch( ViewNotFoundException $e ) {
            echo "<br /><br />". $e->getMessage();
        }
        
        // Show our elapsed time
        Template::Render();
        echo "<br /><br /><small>Page Loaded In: ". Benchmark::ElapsedTime('total_script_exec', 5);
        
        // Practice error
        //throw new ApplicationError('Test Error Message');
    }
}

// Any and all exceptions thrown from the Application should extend the ApplicationError class
class ApplicationError extends Exception {}