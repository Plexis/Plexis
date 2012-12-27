<?php
/*
| ---------------------------------------------------------------
| Example Module Admin Controller
| ---------------------------------------------------------------
*/

class Devtest 
{

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
*/
    public function __construct()
    {
        /* 
            We cannot construct the core controller because the
            Admin panel is loading these functions... so we create
            our own module Constructor. You just need to load what 
            is required to run the install() and uninstall() methods
        */
        
        // In this example, we manually load the loader and database classes
        $this->load = load_class('Loader');
        $this->DB = $this->load->database( 'DB' );
    }

/*
| ---------------------------------------------------------------
| Required Install and Uninstall Methods
| ---------------------------------------------------------------
*/
    // This function is ran when the user installs the module via the admin panel
    // Return TRUE if the module installs correctly, or false
    public function install()
    {
        return true;
    }
    
    // This function is ran when the user Un-installs the module via the admin panel
    // Return TRUE if the module un-installs correctly, or false
    public function uninstall()
    {
        return true;
    }
    
    /* 
        For admin panel integration, this method is REQUIRED. It IS your admin method
        It is not required IF you dont have admin panel integration. When this method
        is called, it essentially takes over the system, and is used to load views etc. 
    */
    public function admin()
    {
        /* 
            Example using the Application instance to load an admin users page:
            
            $Instance = get_instance();
            $Instance->users();
        */

        /*
            Array of variable names to be parsed, example ( $variable_array = (array('test' => 'It Works!')); )
            would replace {test} in the template view file with "It Works!"
        */
        $variable_array = (array('test' => 'It Works! Admin Panel Integration Works Like It Should!'));
        
        /*
            Since this method takes over the system completly, you must load your own view file!
            'module_admin_view' would load this file ( modules/module_name/views/admin_module_view.php )
        */
        $this->load->view('module_admin_view', $variable_array);
        
        // The return value here should be true or false
        return true;  // Tells the admin controller things are OK, and to shutdown
        return false; // Tells the admin controller things went bad, and displays an error to the user
    }
}