<?php
use Library\Template;

class Modules extends Core\Controller
{
    
    public function __construct($Module)
    {
        // Construct the parent controller, providing our module object
        parent::__construct($Module);
        Template::SetThemePath($this->modulePath, 'public');
        $this->loadHelper('admin');
    }
    
    public function index()
    {
        // Set page title and description
        setPageTitle('Module Manager');
        setPageDesc('Install, remove and manage your modules. You may also configure modules that support it.');
        
        // Get alist of installed modules...
        
        Template::Add($this->loadView('module_index'));
    }
}