<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Controller
| ---------------------------------------------------------------
|
| Main Controller file. This file will act as a base for the
| whole system
|
*/
namespace Core;

class Controller
{
    // Our controller name
    public $controller;

    // Our action (sub page)
    public $action;
    
    // Our queryString
    public $querystring;

    // The instance of this class
    private static $instance;
    
    // Out session
    public $Session;
    
    // Our auth class
    public $Auth;
    
    // Event handler
    public $Event;
    
    // language class
    public $Language;
    
    // Template Class
    public $Template;
    
    // Database functions
    public $DB, $RDB;
    
    // Stats Class
    public $Statistics;

/*
| ---------------------------------------------------------------
| Constructor: __construct()
| ---------------------------------------------------------------
|
*/
    public function __construct($autoload = true, $init_template = true) 
    {
        // Set the instance here
        self::$instance = $this;
        
        // Set our Controller and Action
        $this->controller = $GLOBALS['controller'];
        $this->action = $GLOBALS['action'];
        $this->querystring = $GLOBALS['querystring'];
        
        // Initiate the Loader Input, and Config class
        $this->load = load_class('Loader');
        $this->Config = load_class('Config');
        $this->Input = load_class('Input');

        // If site is updating, only allow Ajax requests
        if($GLOBALS['controller'] != 'admin_ajax' && $this->Config->get('site_updating')) 
            die('Site Down for maintenance. Be back soon.');
        
        // Setup the selected users language
        $this->Language = load_class('Language');
        $GLOBALS['language'] = $this->Language->selected_language();
        
        // Autoload helpers and library's
        if($autoload == true) $this->_autoload();
        
        // Setup the template system
        if($init_template == true) $this->_init_template();
        
        // Process DB updates
        if( !$this->Input->is_ajax() ) $this->_process_db();
        
        // Fire the controller event
        load_class('Events')->trigger('controller_init');
    }
    
/*
| ---------------------------------------------------------------
| Method: _autoload()
| ---------------------------------------------------------------
|
*/
    protected function _autoload()
    {
        // Autoload the config autoload_helpers 
        $libs = $this->Config->get('autoload_helpers', 'Core');
        if(count($libs) > 0)
        {
            foreach($libs as $lib)
            {
                $this->load->helper($lib);
            }
        }
        
        // Autoload the config autoload_libraries
        $libs = $this->Config->get('autoload_libraries', 'Core');
        if(count($libs) > 0)
        {
            foreach($libs as $lib)
            {
                $this->load->library($lib);
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: _init_template()
| ---------------------------------------------------------------
|
*/
    protected function _init_template() 
    {
        // Set our template path based on the users selected template
        if($this->controller == 'admin')
        {
            $path = path('system', 'admin');
            $this->Template->set_template_path($path);
        }
        else
        {
            // Check if the user has a selected theme.
            $user = $this->Session->data['user'];
            
            // Load users selected theme if there is one selected
            if($user['logged_in'] == true && !empty($user['selected_theme']))
            {
                // Make sure the tempalate exists before setting the theme
                $query  = "SELECT `status` FROM `pcms_templates` WHERE `name`=?";
                $status = $this->DB->query( $query, array($user['selected_theme']) )->fetch_column();
                
                // If the template exists, and is enabled for site use
                if($status)
                {
                    $path = path('third_party', 'themes', $user['selected_theme']);
                    $this->Template->set_template_path($path);
                    return;
                }
            }
            
            // Set default template path if we are here
            $path = path('third_party', 'themes', config('default_template'));
            $this->Template->set_template_path($path);
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: _process_db() 
| ---------------------------------------------------------------
|
*/
    private function _process_db() 
    {
        // For starts, get our current database version
        $query = "SELECT `value` FROM `pcms_versions` WHERE `key`='database'";
        $version = real_ver( $this->DB->query( $query )->fetch_column() );
        if($version < real_ver( REQ_DB_VERSION ))
        {
            $updates = array();
            $path = path( SYSTEM_PATH, "sql", "updates" );
            
            // Open the __updates directory and scan all updates
            $list = @opendir( $path );
            if($list)
            {
                while(false !== ($file = readdir($list)))
                {
                    if($file[0] != "." && !is_dir( path( $path, $file ) ))
                    {
                        // Format should be like so "update_#.sql
                        $names = explode('_', $file);
                        $update = str_replace('.sql', '', $names[1]);
                        if(real_ver($update) > $version)
                        {
                            $updates[] = array('file' => $file, 'version' => $update);
                        }
                    }
                }
                @closedir($list);
            }
            
            // If we have updates
            if(!empty($updates))
            {
                // Order them by rev
                sort($updates);

                // Process updates
                foreach($updates as $update)
                {
                    if( !$this->DB->utilities->run_sql_file($path . DS . $update['file']) )
                    {
                        log_message('Failed to run SQL file "'. $path . DS . $update['file'] .'" on database', 'error.log');
                        break;
                    }
                    $version = $update['version'];
                }
            }
        }
        
        // Define our REAL db version now, after updates are run
        define('CMS_DB_VERSION', $version);
    }
    
/*
| ---------------------------------------------------------------
| Function: get_instance()
| ---------------------------------------------------------------
|
| Gateway to adding this controller class to an outside file
|
| @Return: (Object) Returns the instance of this class
|
*/
    public static function get_instance()
    {
        return self::$instance;
    }

/*
| ---------------------------------------------------------------
| Function: _beforeAction()
| ---------------------------------------------------------------
|
| Mini hook of code to be called right before the action
|
*/
    public function _beforeAction() {}

/*
| ---------------------------------------------------------------
| Function: _afterAction()
| ---------------------------------------------------------------
|
| Mini hook of code to be called right after the action
|
*/
    public function _afterAction() {}

}
// EOF