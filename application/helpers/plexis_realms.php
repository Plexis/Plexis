<?php
/*
| ---------------------------------------------------------------
| Function: get_realm_cookie()
| ---------------------------------------------------------------
|
| This function returns the users selected realm from his cookie
|
| @Return: (AInt) The Realm ID
|
*/    
    function get_realm_cookie()
    {
        // Load the input class
        $input = load_class('Input');
        $return = $input->cookie('realm_id', TRUE);
        if($return != FALSE)
        {
            // We need to make sure the realm is still installed
            $DB = load_class('Loader')->database( 'DB' );
            $query = "SELECT `name` FROM `pcms_realms` WHERE `id`=?";
            $result = $DB->query( $query, array($return) )->fetch_column();
            
            // If false, Hard set the cookie to default
            if($result == FALSE) goto SetDefault;
        }
        else
        {
            SetDefault:
            {
                // Hard set the cookie to default
                $return = config('default_realm_id');
                $input->set_cookie('realm_id', $return);
                $_COOKIE['realm_id'] = $return;
            }
        }
        return $return;
    }

/*
| ---------------------------------------------------------------
| Function: get_installed_realms()
| ---------------------------------------------------------------
|
| This function is used to return an array of site installed realms.
|
| @Return: (Array) Array of installed realms
|
*/
    function get_installed_realms()
    {
        $load = load_class('Loader');
        $DB = $load->database( 'DB' );
        
        // Build our query
        $query = "SELECT * FROM `pcms_realms`";
        return $DB->query( $query )->fetch_array();
    }
    
/*
| ---------------------------------------------------------------
| Function: realm_installed()
| ---------------------------------------------------------------
|
| This function is used to find out if a realm is installed based on ID
|
| @Return: (Bool) True if the realm is installed, FALSE otherwise
|
*/
    function realm_installed($id)
    {
        $load = load_class('Loader');
        $DB = $load->database( 'DB' );
        
        // Build our query
        $query = "SELECT `name` FROM `pcms_realms` WHERE `id`=?";
        $result = $DB->query( $query, array($id) )->fetch_column();
        
        // Make our return
        if($result == FALSE)
        {
            return FALSE;
        }
        return TRUE;
    }
    
/*
| ---------------------------------------------------------------
| Function: get_realm()
| ---------------------------------------------------------------
|
| This function is used to fetch the realm info based on ID
|
| @Return: (Array) Array of Realm information, FALSE otherwise
|
*/
    function get_realm($id)
    {
        $load = load_class('Loader');
        $DB = $load->database( 'DB' );
        
        // Build our query
        $query = "SELECT * FROM `pcms_realms` WHERE `id`=?";
        $result = $DB->query( $query, array($id) )->fetch_row();
        
        // Make our return
        return $result;
    }
    
/*
| ---------------------------------------------------------------
| Function: get_realm_status()
| ---------------------------------------------------------------
|
| This function is used to return an array of site installed realms.
|
| @Param: (int) $id: The ID of the realm, 0 for an array or all
| @Return: (Array) Array of installed realms
|
*/
    function get_realm_status($id = 0, $cache_time = 300)
    {
        // Check the cache to see if we recently got the results
        $load = load_class('Loader');
        $Cache = $load->library('Cache');
        
        // See if we have cached results
        $result = $Cache->get('realm_status_'.$id);
        if($result == FALSE)
        {
            // If we are here, then the cache results were expired
            $Debug = load_class('Debug');
            $DB = $load->database( 'DB' );
            
            // All realms?
            if($id == 0)
            {
                // Build our query
                $query = "SELECT `id`, `name`, `address`, `port` FROM `pcms_realms`";
            }
            else
            {
                $query = "SELECT `id`, `name`, `address`, `port` FROM `pcms_realms` WHERE `id`=?";
            }
            
            // fetch the array of realms
            $realms = $DB->query( $query )->fetch_array();
            
            // Dont log errors
            $Debug->silent_mode(true);
            
            // Loop through each realm, and get its status
            foreach($realms as $key => $realm)
            {
                $handle = fsockopen($realm['address'], $realm['port'], $errno, $errstr, 1);
                if(!$handle)
                {
                    $realms[$key]['status'] = 0;
                }
                else
                {
                    $realms[$key]['status'] = 1;
                }
            }
            
            // Re-enable errors, and Cache the results for 5 minutes
            $Debug->silent_mode(false);
            $Cache->save('realm_status_'.$id, $realms, $cache_time);
            return $realms;
        }
        return $result;
    }