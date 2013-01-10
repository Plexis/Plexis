<?php

use Core\Controller;
use Core\Database;
use Library\Template;

class Admin extends Controller
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
        setPageTitle('Dashboard');
        setPageDesc('Here you have a quick overview of some features');
        
        // Get our PHP and DB versions
        $info = Database::GetConnection('DB')->serverInfo();

        // Add our build var
        Template::SetJsVar('Build', CMS_REVISION);

        // Proccess DB red font if out of date
        //$db = (REQ_DB_VER != CMS_DB_VER) ? '<font color="red">'. REQ_DB_VER .'</font> (Manual update Required)' : REQ_DB_VER;

        // Set our page data
        $data = array(
            'driver' => ucfirst( $info['driver'] ),
            'php_version' => phpversion(),
            'mod_rewrite' => ( MOD_REWRITE ) ? "Enabled" : "Disabled",
            'database_version' => $info['version'],
            'CMS_VERSION' => CMS_MAJOR_VER .'.'. CMS_MINOR_VER .'.'. CMS_MINOR_REV,
            'CMS_BUILD' => CMS_REVISION,
            'CMS_DB_VERSION' => REQ_DB_VER
        );
        
        $View = $this->loadView("dashboard");
        $View->Set($data);
        Template::Add($View);
    }
}