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
use Core\NotFoundException;
use Core\Request;
use Core\Response;
use Core\Router;
use Library\Auth;
use Library\Template;
use Library\View;

class Plexis
{
    private static $isRunning = false;
    public static $modulePath;
    protected static $realm;
    
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
        
        // Init the configs and database connection
        self::InitConfigsAndDatabase();
        
        // Load Auth class and User
        self::LoadWowlib();
        Auth::Init();
        
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
| Method: Show404()
| ---------------------------------------------------------------
|
| Displays the 404 page not found page
|
*/
    public static function Show404()
    {
        // Clean all current output
        ob_clean();
        
        // Set our status code to 404
        Response::StatusCode(404);
        
        // Get our 404 template contents
        $View = new View( path(SYSTEM_PATH, "errors", "error_404.php") );
        $View->Set('site_url', Request::BaseUrl());
        Response::Body($View);
        
        // Send response, and die
        Response::Send();
        die;
    }
    
/*
| ---------------------------------------------------------------
| Method: ShowSiteOffline()
| ---------------------------------------------------------------
|
| Displays the site offline page
|
*/
    public static function ShowSiteOffline($message = null)
    {
        // Clean all current output
        ob_clean();
        
        // Set our status code to 503 "Service Unavailable"
        Response::StatusCode(503);
        
        // Get our 404 template contents
        $View = new View( path(SYSTEM_PATH, "errors", "error_site_offline.php") );
        $View->Set('site_url', Request::BaseUrl());
        $View->Set('message', $message);
        Response::Body($View);
        
        // Send response, and die
        Response::Send();
        die;
    }
    
/*
| ---------------------------------------------------------------
| Method: GetRealm()
| ---------------------------------------------------------------
|
| Returns the Realm Object
|
*/
    public static function GetRealm()
    {
        return self::$realm;
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
        
        // Define out module path, and our module controller path
        self::$modulePath = path( SYSTEM_PATH, 'modules', strtolower($Request['controller']) );
        Dispatch::SetControllerPath( path(self::$modulePath, 'controllers') );
        
        // Try to execute the controller, and catch any 404 error
        try {
            Dispatch::Execute();
        }
        catch( NotFoundException $e ) {
            self::Show404();
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: LoadWowlib()
| ---------------------------------------------------------------
|
| Internal method for initiating the wowlib
|
*/
    protected static function LoadWowlib()
    {
        // Load the wowlib class file
        require path( SYSTEM_PATH, "wowlib", "wowlib.php" );
        
        // Try to init the wowlib
        $message = null;
        try {
            Wowlib::Init( Config::GetVar('emulator', 'Plexis') );
            self::$realm = Wowlib::GetRealm(0, Config::GetVar('RealmDB', 'DB'));
        }
        catch( Exception $e ) {
            // Template::Message('error', 'Wowlib offline: '. $e->getMessage());
            $message = $e->getMessage();
            self::$realm = false;
        }
        
        if(self::$realm === false)
        {
            if(empty($message)) $message = "Realm Database Offline";
            //self::ShowSiteOffline($message);
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: InitConfigsAndDatabase()
| ---------------------------------------------------------------
|
| Internal method for loading the plexis config files, and initializing
| the Plexis database connection
|
*/
    protected static function InitConfigsAndDatabase()
    {
        // Tell the autoloader something
        AutoLoader::RegisterNamespace('Library', path( SYSTEM_PATH, "library" ));
        
        // Import the constants file
        require path(SYSTEM_PATH, "Constants.php");
        
        // Load the Plexis Config file
        $file = path(SYSTEM_PATH, "config", "config.php");
        Config::Load($file, 'Plexis');
        
        // Load Database config file
        $file = path(SYSTEM_PATH, "config", "database.config.php");
        Config::Load($file, 'DB', 'DB_Configs');
        
        // Init the database connection
        try {
            Database::Connect('DB', Config::GetVar('PlexisDB', 'DB'));
        }
        catch( DatabaseConnectError $e ) {
            $message = $e->getMessage();
            self::ShowSiteOffline('Plexis database offline');
        }
    }
}

// Any and all exceptions thrown from the Application should extend the ApplicationError class
class ApplicationError extends Exception {}