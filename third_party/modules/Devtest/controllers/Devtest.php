<?php
/*
| ---------------------------------------------------------------
| Example Module
| ---------------------------------------------------------------
*/

class Devtest extends Core\Controller 
{

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
*/
    public function __construct($Module)
    {
        // Construct the parent controller, providing our module object
        parent::__construct($Module);
        $this->module = $Module;
    }
    
/*
| ---------------------------------------------------------------
| Page Functions - These are viewed by users in the frontend
| ---------------------------------------------------------------
*/
    
    public function index() 
    {
        $this->module->uninstall();
        $this->module->install(true);
        var_dump( $this->module->isInstalled() ); die;
    }
    
    public function intsize()
    {
        echo PHP_INT_MAX;
        Plexis::RenderTemplate(false);
    }
    
    public function dirtest()
    {
        $path1 = truePath('C:/users/steve/desktop/test/');
        $path2 = truePath('C:/users/steve/desktop/test2/');
        $d = false;
        
        try {
            $d = new Core\IO\DirectoryInfo($path1);
            $d->moveTo($path2);
        }
        catch( \Exception $e ) {
            $d = new Core\IO\DirectoryInfo($path2);
            $d->moveTo($path1);
        }
        var_dump($d);
        
        echo microtime(1) - TIME_START;
        Plexis::RenderTemplate(false);
    }
    
    public function zip()
    {
        $path = path( SYSTEM_PATH );
        $file = path( SYSTEM_PATH, 'system.zip' );
        var_dump(Library\ZipArchive::ZipDirectory($path, $file));
        Plexis::RenderTemplate(false);
    }
    
    public function AddDefaultRoutes()
    {
        $Stack = new Core\Router\RouteCollection();
        $path = path( SYSTEM_PATH, 'modules' );
        
        $d = new Core\IO\DirectoryInfo($path);
        $modules = $d->getDirList();
        
        foreach($modules as $m)
        {
            $Mod = new Core\Module($m->fullpath());
            $Stack->addModuleRoutes( $Mod );
        }
        
        
        $Stack->addModuleRoutes( $this->module );
        var_dump(Core\Router::AddRoutes($Stack, true));
        die;
    }
    
    public function debug()
    {
        var_dump( debug_backtrace() );
        die;
    }
}
?>