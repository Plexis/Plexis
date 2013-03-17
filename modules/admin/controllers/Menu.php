<?php
namespace Admin;

use Core\Controller;
use Core\Database;
use Library\Breadcrumb;
use Library\Template;

final class Menu extends Controller
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
        setPageTitle('Navigation Menu Manager');
        setPageDesc('Add, Edit, Delete & Sort Menu easily');
        
        // Get our PHP and DB versions
        $DB = Database::GetConnection('DB');
        
        // Add breadcrumb
        Breadcrumb::Append('Menu Manager', SITE_URL . '/admin/menu');
        
        // Load Dashboard view
        $View = $this->loadView("menu");
        Template::Add($View);
    }
}