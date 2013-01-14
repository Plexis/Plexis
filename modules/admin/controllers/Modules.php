<?php
namespace Admin;

use Core\Controller;
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
        Template::Add($View);
    }
    
/*
| ---------------------------------------------------------------
| P02: Module Configuration page
| ---------------------------------------------------------------
|
*/
    public function manage($moduleName)
    {
        Template::Add($moduleName);
    }
}