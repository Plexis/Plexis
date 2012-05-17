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
| Class: Admin_Model()
| ---------------------------------------------------------------
|
| Model for the Admin controller
|
*/
class Admin_Model extends Application\Core\Model 
{

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    public function __construct()
    {
        // Load required Libs
        parent::__construct();
        $this->Session = $this->load->library('Session');
        
        // Init a session var
        $this->user = $this->Session->get('user');
        
        // Make sure the user has admin access'
        if( !($this->user['is_admin'] == 1 || $this->user['is_super_admin'] == 1) )
        {
            // Throwing an exception will cause the class init to fail ;)
            throw new Exception('Must be an admin');
            return FALSE;
        }
    }

/*
| ---------------------------------------------------------------
| Method: site_settings_options()
| ---------------------------------------------------------------
|
| Generates the select options in the site settings screen
|
*/    
    public function site_settings_options()
    {
        // Process installed realms
        $query = "SELECT `id`, `name` FROM `pcms_realms`";
        $result = $this->DB->query( $query )->fetch_array();
        
        // Create our realms options
        if($result != FALSE && !empty($result))
        {
            $default = config('default_realm_id');
            foreach($result as $realm)
            {
                // Get our selected option
                $selected = '';
                if($default == $realm['id'])
                {
                    $selected = 'selected="selected" ';
                }
                
                // Add the language folder to the array
                $realms[] = '<option value="'.$realm['id'].'" '. $selected .'>'.$realm['name'].'</option>';
            }
        }
        else
        {
            // No realms installed
            $realms[] = '<option value="0">No realms Installed!</option>';
        }
        
        
        // Process installed templates
        $default = config('default_templates');
        $list = get_installed_templates();
        foreach($list as $file)
        {
            // Get our selected option
            $selected = '';
            $name = $file['name'];
            if($default == $name)
            {
                $selected = 'selected="selected" ';
            }
            
            // Add the language folder to the array
            $templates[] = '<option value="'.$name.'" '. $selected .'>'.$name.'</option>';
        }
        
        // Process languages
        $default = config('default_language');
        $list = get_languages();
        foreach($list as $file)
        {
            // Get our selected option
            $selected = '';
            if($default == $file)
            {
                $selected = 'selected="selected" ';
            }
            
            // Add the language folder to the array
            $languages[] = '<option value="'.$file.'" '. $selected .'>'. ucfirst($file).'</option>';
        }
        
        // Process installed emulators
        $default = config('emulator');
        $list = get_emulators();
        foreach($list as $name)
        {
            // Get our selected option
            $selected = '';
            if($default == $name)
            {
                $selected = 'selected="selected" ';
            }
            
            // Add the language folder to the array
            $emu[] = '<option value="'.$name.'" '. $selected .'>'. ucfirst($name) .'</option>';
        }
        
        return array(
            'realms' => $realms,
            'templates' => $templates,
            'languages' => $languages,
            'emulators' => $emu
        );
    }
    
/*
| ---------------------------------------------------------------
| Method: install_module()
| ---------------------------------------------------------------
|
| Adds a new module to the database and installs it
|
| @Param: (String) $name - The name of the module
| @Param: (String) $uri - The URI link to access the module
| @Param: (String) $method - The method that laods with this $uri
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function install_module($name, $uri, $method)
    {
        // Load the module controller
        $file = APP_PATH . DS . 'modules' . DS . $name . DS .'controller.php';
        if(!file_exists($file))
        {
            return FALSE;
        }
        require $file;
        
        // Init the module into a variable
        $class = ucfirst($name);
        $module = new $class( true );
        
        // Run the module installer
        $result = $module->__install();
        if($result == FALSE) return FALSE;
        
        // Make sure we have a fixed URI
        $uri = rtrim($uri, '/');
        $uri = ltrim($uri, '/');
        if(strpos($uri, '/') === FALSE)
        {
            $uri = $uri .'/index';
        }
        
        // Build out insert data
        $data['name'] = $name;
        $data['uri'] = $uri;
        $data['method'] = $method;
        $data['has_admin'] = $module->__has_admin();
        
        // Insert our post
        return $this->DB->insert('pcms_modules', $data);
    }
    
/*
| ---------------------------------------------------------------
| Method: uninstall_module()
| ---------------------------------------------------------------
|
| Uninstalls a modulde from site use
|
| @Param: (String) $name - The name of the module
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function uninstall_module($name)
    {
        // Load the module controller
        $file = APP_PATH . DS . 'modules' . DS . $name . DS .'controller.php';
        if(!file_exists($file))
        {
            return FALSE;
        }
        require $file;
        
        // Init the module into a variable
        $class = ucfirst($name);
        $module = new $class( true );
        
        // Run the module installer
        $result = $module->__uninstall();
        if($result == FALSE) return FALSE;
        
        // Delete our post and return the result
        $name = strtolower($name);
        return $this->DB->delete('pcms_modules', "`name`='$name'");
    }
    
/*
| ---------------------------------------------------------------
| Method: install_template()
| ---------------------------------------------------------------
|
| Installs a template for site use
|
| @Param: (String) $id - The template id in templates table
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function install_template($id)
    {
        // Set our templates status to 1
        return $this->DB->update('pcms_templates', array('status' => 1), "`id`=$id");
    }
    
/*
| ---------------------------------------------------------------
| Method: uninstall_template()
| ---------------------------------------------------------------
|
| Uninstalls a template from site use
|
| @Param: (String) $id - The template id in templates table
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function uninstall_template($id)
    {
        // Set the status to 0
        return $this->DB->update('pcms_templates', array('status' => 0), "`id`=$id");
    }
}
// EOF