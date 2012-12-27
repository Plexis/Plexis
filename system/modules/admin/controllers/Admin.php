<?php

use Core\Controller;
use Core\Database;
use Library\Template;

class Admin extends Controller
{
    public function __construct()
    {
        parent::__construct(__FILE__);
        Template::SetTheme('admin');
    }
    
    public function index()
    {
        // Get our PHP and DB versions
        $info = Database::GetConnection('DB')->serverInfo();

        // Add our build var
        Template::SetJsVar('Build', CMS_REVISION);
        Template::SetVar('page_title', 'Dashboard');
        Template::SetVar('page_desc', 'Here you have a quick overview of some features');

        // Proccess DB red font if out of date
        //$db = (REQ_DB_VERSION != CMS_DB_VERSION) ? '<font color="red">'. REQ_DB_VERSION .'</font> (Manual update Required)' : REQ_DB_VERSION;

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