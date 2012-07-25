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
    private static $instance = false;

/*
| ---------------------------------------------------------------
| Constructor: __construct()
| ---------------------------------------------------------------
|
*/
    public function __construct($autoload = true, $init_template = true) 
    {
        // Add trace for debugging
        \Debug::trace('Initializing core controller...', __FILE__, __LINE__);
        
        // Set the instance here
        if(self::$instance == false) self::$instance = $this;
        
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
        
        // Autoload helpers and libraries
        if($autoload == true) $this->_autoload();
        
        // Setup the template system
        if($init_template == true) $this->_init_template();
        
        // Process DB updates
        if( !$this->Input->is_ajax() ) $this->_process_db();
        
        // Add trace for debugging
        \Debug::trace('Core controller initialized successfully', __FILE__, __LINE__);
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
        $c = count($libs);
        if($c > 0)
        {
            // Add trace for debugging
            \Debug::trace('Autoloading '. $c .' helper(s)', __FILE__, __LINE__);
            foreach($libs as $lib)
            {
                $this->load->helper($lib);
            }
        }
        
        // Autoload the config autoload_libraries
        $libs = $this->Config->get('autoload_libraries', 'Core');
        $c = count($libs);
        if($c > 0)
        {
            // Add trace for debugging
            \Debug::trace('Autoloading '. $c .' library class(es)', __FILE__, __LINE__);
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
            $user = $this->User->data;
            
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
        $version = real_ver( $this->DB->query( $query )->fetchColumn() );
        if($version < real_ver( REQ_DB_VERSION ))
        {
            $updates = array();
            $path = path( SYSTEM_PATH, "sql", "updates" );
            
            // Add trace for debugging
            \Debug::trace("Updating core database from {$version} to ". REQ_DB_VERSION, __FILE__, __LINE__);
            
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
                    if( !$this->DB->utilities->runSqlFile($path . DS . $update['file']) )
                    {
                        // Add trace for debugging
                        \Debug::trace('Database update to version '. $version .' failed!', __FILE__, __LINE__);
                        log_message('Failed to run SQL file "'. $path . DS . $update['file'] .'" on database', 'error.log');
                        break;
                    }
                    
                    // Add trace for debugging
                    \Debug::trace('Database successfully updated to version '. $version, __FILE__, __LINE__);
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