<?php
/*
| ---------------------------------------------------------------
| Function: add_permission
| ---------------------------------------------------------------
|
| This function adds a new permissison to the database
|
| @Param: (String) $key - The permissions Key (id) 
| @Param: (String) $name - The name of the permission
| @Param: (String) $desc - A description of the permisssion
| @Param: (String) $mod - The module tied to this permission
| @Return (Bool) True on success, false otherwise
|
*/
    function add_permission($key, $name, $desc, $mod)
    {
        // Define this for multiple calls
        static $DB = null;
        
        // Load the database if not already
        if($DB == NULL)
        {
            $load = load_class('Loader');
            $DB = $load->database( 'DB' );
        }
        
        // Add the permission to the database
        $data = array(
            'key' => $key,
            'name' => $name,
            'desc' => $desc,
            'module' => $mod
        );
        return $DB->insert('pcms_permissions', $data);
    }
    
/*
| ---------------------------------------------------------------
| Function: remove_permission
| ---------------------------------------------------------------
|
| This fucntion removes a permission from the database
|
| @Param: (String) $key - The permissions Key (id)
| @Return (Bool) True on success, false otherwise 
|
*/
    function remove_permission($key)
    {
        // Define this for multiple calls
        static $DB = null;
        
        // Load the database if not already
        if($DB == NULL)
        {
            $load = load_class('Loader');
            $DB = $load->database( 'DB' );
        }
        
        // Remove the permission from the database
        return $DB->delete('pcms_permissions', "`key` = '". $key ."'");
    }
    
/*
| ---------------------------------------------------------------
| Function: remove_permissions_by_module
| ---------------------------------------------------------------
|
| This function removes all permissions from the database that
| are tied to the $mod module
|
| @Param: (String) $mod - The permissions module key
| @Return (Bool) True on success, false otherwise 
|
*/
    function remove_permissions_by_module($mod)
    {
        // Define this for multiple calls
        static $DB = null;
        
        // Load the database if not already
        if($DB == NULL)
        {
            $load = load_class('Loader');
            $DB = $load->database( 'DB' );
        }
        
        // Remove the permission from the database
        return $DB->delete('pcms_permissions', "`module` = '". $mod ."'");
    }