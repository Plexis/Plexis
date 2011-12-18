<?php
class Admin_Model extends Application\Core\Model 
{
    // We want only admins to have access here
    protected $is_admin = TRUE;
/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    function __construct()
    {
        // Load required Libs
        parent::__construct();
        $this->Session = $this->load->library('Session');
        
        // Init a session var
        $this->user = $this->Session->get('user');
        
        // Make sure the user has admin access'
        if( !($this->user['is_admin'] == 1 || $this->user['is_super_admin'] == 1) )
        {
            $this->is_admin == FALSE;
            die();
        }
    }
    
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
        
        
        // Process languages
        $default = config('default_language');
        $list = scandir(APP_PATH . DS . 'language');
        foreach($list as $file)
        {
            // Dont include files or ".." / "."
            if($file == "." || $file == "..") continue;
            if(is_file($file)) continue;
            
            // Get our selected option
            $selected = '';
            if($default == $file)
            {
                $selected = 'selected="selected" ';
            }
            
            // Add the language folder to the array
            $languages[] = '<option value="'.$file.'" '. $selected .'>'.$file.'</option>';
        }
        
        return array('realms' => $realms, 'languages' => $languages);
    }
}
// EOF