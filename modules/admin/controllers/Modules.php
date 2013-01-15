<?php
namespace Admin;

use Core\Controller;
use Core\Response;
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
            
        // Breadcrumbs
        Breadcrumb::Append('Modules', SITE_URL . '/admin/modules');
        Breadcrumb::Append('Manage', SITE_URL . '/admin/modules');
        Breadcrumb::Append(ucfirst($moduleName), SITE_URL . '/admin/modules/'. $moduleName);
    }
}