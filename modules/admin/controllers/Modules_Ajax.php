<?php
namespace Admin;

use Core\Controller;
use Core\Database;
use Core\Module;
use Core\Request;
use Core\Router;
use Core\IO\DirectoryInfo;
use Core\Router\RouteCollection;

final class Modules_Ajax extends Controller
{
    protected $success = false;
    protected $type = "error";
    protected $message = null;
    
    public function __construct($Module)
    {
        // Construct the parent controller, providing our module object
        parent::__construct($Module);
    }
    
/*
| ---------------------------------------------------------------
| P01: Fetch Module List
| ---------------------------------------------------------------
|
*/
    public function getlist()
    {
        // Get alist of installed modules...
        $this->DB = Database::GetConnection('DB');
        $installed = $this->DB->query("SELECT `name` FROM `pcms_modules`")->fetchAll( \PDO::FETCH_COLUMN );
        
        // Grab list of all present module for comparison
        $path = path( ROOT, 'modules' );
        $Dir = new DirectoryInfo($path);
        
        // Get module list
        $ModuleList = $Dir->getDirList();
        $modules = array();
        $url = Request::BaseUrl() . '/'. $this->moduleUri .'/public';
        
        // Format found modules into an array
        foreach($ModuleList as $Dir)
            $found[] = $Dir->name();
        
        // Initial output
        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => sizeof($found),
            "iTotalDisplayRecords" => count($found),
            "aaData" => array()
        );
        
        // Load each module
        foreach($found as $mod)
        {
            // Load the module, and determine if it is installed
            $Module = Module::Get($mod);
            $Xml = $Module->getModuleXml();
            $isInstalled = (in_array($mod, $installed));
            
            // blacklist certain modules
            if($mod == 'admin')
            {
                --$output['iTotalRecords'];
                --$output['iTotalDisplayRecords'];
                continue;
            }
            else
            {
                $output['aaData'][] = array(
                    0 => (string) $Xml->info->name,
                    1 => (string) $Xml->info->author,
                    2 => (string) $Xml->info->version,
                    3 => ($isInstalled) 
                        ? '<center><img src="'. $url .'/img/icons/small/tick-circle.png" /></center>' 
                        : '<center><img src="'. $url .'/img/icons/small/cross-circle.png" /></center>',
                    4 => ($isInstalled) 
                        ? '<center><a href="'. SITE_URL .'/admin/modules/manage/'. $mod .'" rel="tooltip" title="Manage Module">
                            <img src="'. $url .'/img/icons/small/settings.png" /></a></center>' 
                        : '',
                    5 => ($isInstalled) 
                        ? '<center><a class="un-install" name="'. $mod .'" href="#" rel="tooltip" title="Uninstall Module">
                            <img src="'. $url .'/img/icons/small/remove.png" /></a></center>' 
                        : '<center><a class="install" name="'. $mod .'" href="#" rel="tooltip" title="Install Module">
                            <img src="'. $url .'/img/icons/small/add.png" /></a></center>'
                );
            }
        }
        
        echo json_encode($output);
    }
    
/*
| ---------------------------------------------------------------
| P02: Install Module
| ---------------------------------------------------------------
|
*/
    public function install()
    {
        // Get our module name
        $post = Request::Post('name', false);
        if($post == false)
        {
            $this->message = "No Post Data!";
            $this->output();
        }
        
        // Declare quit var
        $quit = false;
        
        // Init the module
        try {
            $Module = Module::Get($post);
        }
        catch( ModuleNotFoundException $e ) {
            $this->message = "Module does not exist.";
            $quit = true;
        }
        
        // If error, then quit
        if($quit)
            $this->output();
        
        // Install the module
        try {
            $Module->install();
        }
        catch( \Exception $e ) {
            $this->success = false;
            $this->type = 'error';
            $this->message = $e->getMessage();
            $this->output();
        }
        
        // If we are here, we were successful
        $this->success = true;
        $this->type = 'success';
        $this->message = 'Module installed successfully!';
        
        // Output
        $this->output();
    }
    
/*
| ---------------------------------------------------------------
| P03: Remove Module
| ---------------------------------------------------------------
|
*/
    public function uninstall()
    {
        // Get our module name
        $post = Request::Post('name', false);
        if($post == false)
        {
            $this->message = "No Post Data!";
            $this->output();
        }
        
        // Declare quit var
        $quit = false;
        
        // Init the module
        try {
            $Module = new Module($post);
        }
        catch( ModuleNotFoundException $e ) {
            $this->message = "Module does not exist.";
            $quit = true;
        }
        
        // If error, then quit
        if($quit)
            $this->output();
            
        // If we arent installed, just return true
        if(!$Module->isInstalled())
        {
            $this->success = true;
            $this->type = 'success';
            $this->message = 'Module was already Uninstalled.';
            $this->output();
        }
        
        // Run uninstaller
        try {
            $Module->uninstall();
        }
        catch( \Exception $e ) {
            $this->success = false;
            $this->type = 'error';
            $this->message = $e->getMessage();
            $this->output();
        }
        
        // If we are here, we were successful
        $this->success = true;
        $this->type = 'success';
        $this->message = 'Module Uninstalled';
        
        // Output
        $this->output();
    }
    
    
/*
| ---------------------------------------------------------------
| Output formatter Method
| ---------------------------------------------------------------
|
*/
    protected function output()
    {
        // Remove error tag on success, but allow warnings
        if($this->success == TRUE && $this->type == 'error') 
            $this->type = 'success';

        // Output to the browser in Json format
        echo json_encode(
            array(
                'success' => $this->success,
                'message' => $this->message,
                'type' => $this->type,
                'conflicts' => $this->conflicts
            )
        );
        die;
    }
}