<?php
/* 
| --------------------------------------------------------------
| 
| Plexis CMS
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
| ---------------------------------------------------------------
| Class: Controller
| ---------------------------------------------------------------
|
| Main Controller file. This file will act as a base for the
| whole system
|
*/
namespace Application\Core;

class Controller extends \System\Core\Controller
{

/*
| ---------------------------------------------------------------
| Constructer: __construct()
| ---------------------------------------------------------------
|
*/
    public function __construct() 
    {
        // Check if the install folder is still local
        if(file_exists( ROOT . DS . 'install/index.php'))
        {
            redirect('install/index.php');
            die();
        }
        
        // Build the Core Controller
        parent::__construct();
        
        // load module config file if there is one
        if($GLOBALS['is_module'] == TRUE)
        {
            load_module_config($GLOBALS['controller']);
        }
        
        // Setup the selected users language
        $GLOBALS['language'] = selected_language();
        
        // Process DB updates
        $this->_process_db();
        
        // Setup the template system
        $this->_init_template();
    }
    
/*
| ---------------------------------------------------------------
| Funtion: _init_template()
| ---------------------------------------------------------------
|
*/
    private function _init_template() 
    {
        // Setup our template system
        if($this->controller == 'admin')
        {
            $this->Template->set_template_path('admin');
            $this->Template->config( array('controller_view_paths' => FALSE) );
        }
        // elseif($this->controller == 'ajax')
        // {
            // return;
        // }
        else
        {
            // Check if the user has a selected theme.
            $user = $this->Session->data['user'];
            if($user['logged_in'] == FALSE)
            {
                // Set default template path
                $this->Template->set_template_path('templates' . DS . config('default_template'));
            }
            else
            {
                if(!empty($user['selected_theme']))
                {
                    // Make sure the tempalate exists before setting the theme
                    $query = "SELECT * FROM `pcms_templates` WHERE `name`=?";
                    $template = $this->DB->query( $query, array($user['selected_theme']) )->fetch_row();
                    
                    // If the template exists, and is enabled for site use
                    if($template != FALSE && $template['status'] == 1)
                    {
                        $this->Template->set_template_path('templates' . DS . $template['name']);
                        return;
                    }
                }
                // Set default template path if we are here
                $this->Template->set_template_path('templates' . DS . config('default_template'));
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| Funtion: _process_db() 
| ---------------------------------------------------------------
|
*/
    private function _process_db() 
    {
        // Foir starts, get our current database version
        $query = "SELECT `value` FROM `pcms_versions` WHERE `key`='database'";
        $version = $this->DB->query( $query )->fetch_column();
        if($version < CMS_DB_VERSION)
        {
            $updates = array();
            $path = ROOT . DS . '__updates';
            
            // Open the __updates directory and scan all updates
            $list = @opendir( $path );
            if($list)
            {
                while($file = readdir($list))
                {
                    if($file[0] != "." && is_file($path . DS . $file))
                    {
                        // Format should be like so "update_#.sql
                        $names = explode('_', $file);
                        if($names[1] > $version)
                        {
                            $updates[] = $file;
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
                foreach($updates as $file)
                {
                    $this->DB->utilities->run_sql_file($path . DS . $file);
                }
            }
        }
    }
}
// EOF