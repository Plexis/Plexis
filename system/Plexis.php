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
        
        // Set default theme path (temporary)
        Template::SetThemePath( path(ROOT, "third_party", "themes", "default") );
        
        // Init the plexis config files
        self::LoadConfigs();
        
        // Load Plugins
        self::LoadPlugins();
        
        // Load the Wowlib
        self::LoadWowlib(false);
        
        // Init the database connection, we check to see if it exists first, because
        // a plugin might have already loaded it
        if(Database::GetConnection('DB') === false)
            self::LoadDBConnection();
        
        // Start the Client Auth class
        Auth::Init();
        
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
        
        // Reset all headers, and set our status code to 404
        Response::Reset();
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
        
        // Reset all headers, and set our status code to 503 "Service Unavailable"
        Response::Reset();
        Response::StatusCode(503);
        
        // Get our site offline template contents
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
| Method: LoadDBConnection()
| ---------------------------------------------------------------
|
| Internal method for loading the Plexis DB connection
|
*/
    public static function LoadDBConnection($showOffline = true)
    {
        $conn = false;
        try {
            $conn = Database::Connect('DB', Config::GetVar('PlexisDB', 'DB'));
        }
        catch( DatabaseConnectError $e ) {
            if($showOffline)
            {
                $message = $e->getMessage();
                self::ShowSiteOffline('Plexis database offline');
            }
        }
        
        return $conn;
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
| Method: LoadPlugins()
| ---------------------------------------------------------------
|
| Internal method for loading, and running all plugins
|
*/
    protected static function LoadPlugins()
    {
        // Include our plugins file, and get the size
        include path( SYSTEM_PATH, 'config', 'plugins.php' );
        $OrigSize = sizeof($Plugins);
        
        // Loop through and run each plugin
        $i = 0;
        foreach($Plugins as $name)
        {
            $file = path( ROOT, 'third_party', 'plugins', $name .'.php');
            if(!file_exists($file))
            {
                // Remove the plugin from the list
                unset($Plugins[$i]);
                continue;
            }
            
            include $file;
            $className = "Plugin\\". $name;
            new $className();
            $i++;
        }
        
        // If we had to remove plugins, then save the plugins file
        if(sizeof($Plugins) != $OrigSize)
        {
            $file = path( SYSTEM_PATH, 'config', 'plugins.php' );
            $source = "<?php\n\$Plugins = ". var_export($Plugins, true) .";\n?>";
            file_put_contents($file, $source);
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
    protected static function LoadWowlib($showOffline = true)
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
        
        // If the realm is offline, show the site offline screen
        if(self::$realm === false)
        {
            if($showOffline)
            {
                if(empty($message)) $message = "Realm Database Offline";
                self::ShowSiteOffline($message);
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: LoadConfigs()
| ---------------------------------------------------------------
|
| Internal method for loading the plexis config files
|
*/
    protected static function LoadConfigs()
    {
        // Import the Versions file
        require path(SYSTEM_PATH, "Versions.php");
        
        // Load the Plexis Config file
        $file = path(SYSTEM_PATH, "config", "config.php");
        Config::Load($file, 'Plexis');
        
        // Load Database config file
        $file = path(SYSTEM_PATH, "config", "database.config.php");
        Config::Load($file, 'DB', 'DB_Configs');
        
        // Define our site url
        if( MOD_REWRITE )
            define('SITE_URL', Request::BaseUrl());
        else
            define('SITE_URL', Request::BaseUrl() .'/?uri=');
    }
}

// Any and all exceptions thrown from the Application should extend the ApplicationError class
class ApplicationError extends Exception {}