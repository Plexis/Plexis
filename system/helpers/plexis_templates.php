<?php
/*
| ---------------------------------------------------------------
| Function: get_templates()
| ---------------------------------------------------------------
|
| This function is used to return an array of template fodlers 
|   in the templates folder
|
| @Return: (Array) Array of template names
|
*/
    function get_templates()
    {
        $reallist = array();
        $list = load_class('Filesystem', 'library')->list_folders(APP_PATH . DS . 'templates');
        foreach($list as $file)
        {
            $reallist[] = $file;
        }
        return $reallist;
    }

/*
| ---------------------------------------------------------------
| Function: get_installed_templates()
| ---------------------------------------------------------------
|
| This function is used to return an array of site installed templates.
|
| @Return: (Array) Array of installed templates
|
*/
    function get_installed_templates()
    {
        $load = load_class('Loader');
        $DB = $load->database( 'DB' );
        
        // Build our query
        $query = "SELECT * FROM `pcms_templates` WHERE `status` = 1";
        return $DB->query( $query )->fetch_array();
    }
    
/*
| ---------------------------------------------------------------
| Function: template_installed()
| ---------------------------------------------------------------
|
| This function is used to find out if a templates is installed based 
| on the templates name
|
| @Return: (Bool) True if the template is installed, FALSE otherwise
|
*/
    function template_installed($name)
    {
        $load = load_class('Loader');
        $DB = $load->database( 'DB' );
        
        // Build our query
        $query = "SELECT `status` FROM `pcms_templates` WHERE `name`=?";
        $result = $DB->query( $query, array($name) )->fetch_column();
        return ($result == FALSE) ? FALSE : TRUE;
    }