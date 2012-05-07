<?php
/*
| ---------------------------------------------------------------
| Example Module
| ---------------------------------------------------------------
*/

class Devtest extends \Application\Core\Controller 
{

/*
| ---------------------------------------------------------------
| Required Constructer. Must contain at least the following!
| ---------------------------------------------------------------
*/
    public function __construct($admin_operations = false)
    {
        /*    
            This IF / ELSE is required! Basically, when the module
            is being handled by the admin panel, you CANNOT construct
            the parent controller because its already started and is
            Statically stored, causing an error 
        */
        if($admin_operations == false)
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
            $this->Config->get('var_name', 'mod_config');
            // OR
            config('var_name', 'mod_config');
        }
        else
        {
            /* 
                Admin panel is loading these functions... Cant
                construct the application controller so we create
                our own module install / uninstall / admin constructer
                You just need to load what is required to run the
                __install(), __uninstall(), and admin() functions 
            */
            
            // In this example, we manually load the loader and database classes
            $this->load = load_class('Loader');
            $this->DB = $this->load->database( 'DB' );
        }
    }

/*
| ---------------------------------------------------------------
| Required Install, Uninstall, and Has Admin functions
| ---------------------------------------------------------------
*/
    // This function is ran when the user installs the module via the admin panel
    public function __install()
    {
        // Create an example table using the database forge extension
        $table = $this->DB->forge->create_table('test');
        $table->add_column('id', 'int', 15, array('primary' => TRUE, 'increments' => TRUE, 'unsigned' => TRUE));
        $table->add_column('test', 'string', 100, array('default' => 'test'));
        return $table->execute();
    }
    
    // This function is ran when the user Un-installs the module via the admin panel
    public function __uninstall()
    {
        return $this->DB->forge->drop_table('test');
    }
    
    // This function is used by the admin controller to determine whether the user can
    // use an integrated admin panel for this module
    public function __has_admin()
    {
        // Return TRUE is there is an admin panel integration method ( __admin() ),
        // Or false if there isnt an admin panel integration
        return TRUE;
    }
    
    /* 
        For admin panel integration, this function is REQUIRED. It IS your admin method
        It is not required IF you dont have admin panel integration. When this method
        is called, it essentially takes over the system, and is used to load views etc. 
        
        
        The $Instance variable is the entire system loaded into just that variable
        The $subpage variable is filled when the user enters a subpage under your system
        example: admin/modules/modulename/$subpage 
    */
    public function __admin( $Instance, $subpage )
    {
        /* 
            Example using the Application instance to load an admin users page
            The admin controller is passed in its current state with the $Instance
            variable. 
        */
        //$Instance->users();

        // Array of variable names to be parsed, example ( $variable_array = (array('test' => 'It Works!')); )
        // would replace {test} in the template view file with "It Works!"
        $variable_array = (array('test' => 'It Works! Admin Panel Integration Works Like It Should!'));
        
        // Since this method takes over the system completly, you must load your own view file!
        // 'module_admin_view' would load this file ( modules/module_name/views/admin_module_view.php )
        $this->load->view('module_admin_view', $variable_array);
        
        // NOTE, you may also use $Instance->load->view('module_admin_view', $variable_array);
        
        // The return value here should be true or false
        return true;  // Tells the admin controller things are OK, and to shutdown
        return false; // Tells the admin controller things went bad, and displays an error to the user
    }
    
/*
| ---------------------------------------------------------------
| Page Functions - These are viewed by users in the frontend
| ---------------------------------------------------------------
*/
    
    public function index($mode = 1) 
    {
        // blah blah blah
        $this->load->view('index');
    }
}
?>