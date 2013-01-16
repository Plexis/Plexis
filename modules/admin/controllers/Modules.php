<?php
namespace Admin;

use Core\Controller;
use Core\Response;
use Core\Module;
use Library\Breadcrumb;
use Library\Template;

final class Modules extends Controller
{
    
    public function __construct($Module)
    {
        // Construct the parent controller, providing our module object
        parent::__construct($Module);
        Template::SetThemePath($this->modulePath, 'public');
        $this->loadHelper('admin');
    }
    
/*
| ---------------------------------------------------------------
| P01: Index Modules Page
| ---------------------------------------------------------------
|
*/
    public function index()
    {
        // Set page title and description
        setPageTitle('Module Manager');
        setPageDesc('Install, remove and manage your modules. You may also configure modules that support it.');
        
        // Add the module index script, and load view
        $this->addScript('module_index');
        $View = $this->loadView('module_index');
        
        // Add breadcrumb
        Breadcrumb::Append('Modules', SITE_URL . '/admin/modules');
        
        Template::Add($View);
    }
    
/*
| ---------------------------------------------------------------
| P02: Module Configuration page
| ---------------------------------------------------------------
|
*/
    public function manage($moduleName = null)
    {
        // Make sure we have the module name!
        if(empty($moduleName))
            Response::Redirect('admin/modules', 0, 307);
            
        // Page title and Desc
        setPageTitle(ucfirst($moduleName).' Module Management');
        setPageDesc('&nbsp;');
        
        // Breadcrumbs
        Breadcrumb::Append('Modules', SITE_URL . '/admin/modules');
        Breadcrumb::Append(ucfirst($moduleName), SITE_URL . '/admin/modules/'. $moduleName);
        
        // Load the module
        $Module = $message = false;
        try {
            $Module = Module::Get($moduleName);
        }
        catch( \ModuleNotFoundException $e ) {}
        
        // Of the module doesnt exist, show error
        if($Module === false)
        {
            Response::Redirect('admin/modules', 3);
            Template::Message('error', "Module {$moduleName} does not exist! Redirecting...");
            return;
        }
        
        // Make sure the module is installed
        if(!$Module->isInstalled())
        {
            Response::Redirect('admin/modules', 3);
            Template::Message('error', "Module {$moduleName} is not installed! Modules cannot be configured unless they are installed. Redirecting...");
            return;
        }
        
        // Run the modules admin controller if it has one
        $args = func_get_args();
        $action = (isset($args[1])) ? $args[1] : 'index';
        $params = array_slice($args, 2);
        
        // Invoke the admin controller
        try {
            $Module->invoke('Admin', $action, $params);
        }
        catch( \Exception $e ) {
            \Plexis::Show404();
        }
    }
}