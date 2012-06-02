<?php
/*
| ---------------------------------------------------------------
| Example Module
| ---------------------------------------------------------------
*/

class Devtest extends \Core\Controller 
{

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
*/
    public function __construct()
    {
        // Normally construct the application controller
        parent::__construct();
        
        // !!! Your module cunstructer here !!! //
        
        /* 
            Module template option ('module_view_paths') When set to true, the template system will load
            the pages view file from the the templates module view folder if it exists: 
            ( template_path/module_views/module_name/viewname.php )
            
            If it doesnt exist, then it loads the default module view file: 
            ( modules/module_name/views/viewname.php )
            
            If set to false, it will load the default view for the URI 
            ( template_path/views/controller/viewname.php ) ) 
        */

        $this->Template->config('module_view_paths', true);
        
        /*    
            Example loading a module config file
            First Param => 'Module Name', 
            Second Param => 'Config Array Name', ( !! Must be Unique! Cannot be 'Core', 'App', OR 'DB' !! )
            Third Param => 'Config File Name', 
            Forth Param => 'Array Variable Name' ( !! ONLY IF config options are in an array !! ) 
        */
        load_module_config('Devtest', 'mod_config', 'config.php', 'config_options');
        
        // Usage
        $this->Config = load_class('Config');
        $this->Config->get('var_name', 'mod_config');
        // OR
        config('var_name', 'mod_config');
    }
    
/*
| ---------------------------------------------------------------
| Page Functions - These are viewed by users in the frontend
| ---------------------------------------------------------------
*/
    
    public function index() 
    {
        // blah blah blah
        $this->load->view('index');
    }
}
?>