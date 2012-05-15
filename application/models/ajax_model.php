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
| Class: Ajax_Model()
| ---------------------------------------------------------------
|
| Model for the Ajax controller
|
*/
class Ajax_Model extends Application\Core\Model 
{

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    public function __construct()
    {
        parent::__construct();
    }
    
/*
| ---------------------------------------------------------------
| Method: update_account()
| ---------------------------------------------------------------
|
| Bans a user account
|
*/    
    public function admin_update_account($id, $user)
    {
        // Load our Input library and Ajax controller
        $input = load_class('Input');
        $Ajax = get_instance();
        
        // Init a session var
        $this->user = $Ajax->Session->get('user');
        if($this->user['is_admin'] != 1 || $this->user['is_super_admin'] != 1)
        {
            return FALSE;
        }
		
		//Set up our variables.
        $data = array();
        $changes = FALSE; 
        
        // Grab all POST fields
        $update['email'] = $input->post('email', TRUE);
        $update['group_id'] = $input->post('group_id', TRUE);
        $expansion = $input->post('expansion', TRUE);
		$password = array( $input->post('password1', TRUE), $input->post('password2', TRUE) );
        
        // Grab the user account
        $account = $this->realm->fetch_account($id);

        // Update password if there was a change
        if(!empty($password[0]) && !empty($password[1]))
        {
            if( $password[0] == $password[1] )
            {
                $changes = TRUE;
                $result = $this->realm->change_password($id, $password[0]);
                if( $result === FALSE )
                {
                    $Ajax->output(false, 'account_update_error');
                    return;
                }
            }
            else
            {
                $Ajax->output(false, 'account_update_error');
                return;
            }
        }	
        
        // Update expansion
        if( $expansion != $account['expansion'])
        {
            $changes = TRUE;
            $result = $this->realm->update_expansion($expansion, $id);
            if( $result === FALSE )
            {
                $Ajax->output(false, 'account_update_error');
                return;
            }
        }
        
        // Check for pcms_accounts changed data
        foreach($update as $key => $value)
        {
            if($user[$key] != $value)
            {
                $changes = TRUE;
                $data[$key] = $value;
            }
        }
        
        // If we have updates for the plexis DB, make them
        if(!empty($data))
        {
            $changes = TRUE;
            $result = $this->DB->update('pcms_accounts', $data, '`id`='.$id);
            if( $result === FALSE )
            {
                $Ajax->output(false, 'account_update_error');
                return;
            }
        }
        
        // No updates
        ($changes == TRUE) ? $Ajax->output(true, 'account_update_success') : $Ajax->output(false, 'account_update_nochanges', 'warning');
        return;
    }
    
/*
| ---------------------------------------------------------------
| Method: process_realm()
| ---------------------------------------------------------------
|
| Adds a new realm to the database and installs it
|
| @Param: (String) $action - The mode
|
*/
    public function process_realm($action)
    {
        // Load our config class
        $Config = load_class('Config');
        $Debug = load_class('Debug');
        $Ajax = get_instance();

        // Get our posted information
        $id = $_POST['id'];
        $name = $_POST['name'];
        $address = $_POST['address'];
        $port = $_POST['port'];
        $max = $_POST['max_players'];
        $type = $_POST['type'];
        $driver = $_POST['driver'];
        
        // Build our DB Arrays
        $cs = array(
            'driver'	   => $_POST['c_driver'],
            'host'         => $_POST['c_address'],
            'port'         => $_POST['c_port'],
            'username'     => $_POST['c_username'],
            'password'     => $_POST['c_password'],
            'database'     => $_POST['c_database']
        );
        $ws = array(
            'driver'	   => $_POST['w_driver'],
            'host'         => $_POST['w_address'],
            'port'         => $_POST['w_port'],
            'username'     => $_POST['w_username'],
            'password'     => $_POST['w_password'],
            'database'     => $_POST['w_database']
        );
        $ra = array(
            'type'         => $_POST['ra_type'],
            'port'         => $_POST['ra_port'],
            'username'     => $_POST['ra_username'],
            'password'     => $_POST['ra_password'],
            'urn'          => $_POST['ra_urn']
        );
        $rates = array(
            'description'   => $_POST['rates_desc'],
            'xp'            => $_POST['rates_xp'],
            'drop'          => $_POST['rates_drop'],
            'gold'          => $_POST['rates_gold'],
            'professions'   => $_POST['rates_professions'],
            'reputation'    => $_POST['rates_reputation'],
            'honor'         => $_POST['rates_honor']
        );

        // Test our new connections before saving to config
        $good = TRUE;
        if( !$this->load->database($cs, false, true) ) $good = FALSE;
        if( !$this->load->database($ws, false, true) ) $good = FALSE;

        // If manually installing, lets get our unique id
        if($action == 'manual-install')
        {
            $result = $this->realm->realmlist();
            $installed = get_installed_realms();
            if( !empty($result) )
            {
                $highest = end($result);
                if( empty($installed) )
                {
                    $id = $highest['id'] + 1;
                }
                else
                {
                    $high2 = end($installed);
                    ($highest['id'] > $high2['id']) ? $id = $highest['id'] + 1 : $id = $high2['id'] + 1;
                }
            }
            else
            {
                ( !empty($installed) ) ? $id = $high2['id'] + 1 : $id = 1;
            }
        }
        
        // Install our new stuffs
        $data = array(
            'id' => $id,
            'name' => $name,
            'address' => $address,
            'port' => $port,
            'type' => $type,
            'max_players' => $max,
            'driver' => $driver,
            'rates' => serialize($rates),
            'char_db' => serialize($cs),
            'world_db' => serialize($ws),
            'ra_info' => serialize($ra)
        );
        
        // Process our return message
        if($action == 'install' || $action == 'manual-install')
        {
            $result = $this->DB->insert('pcms_realms', $data);
            if($result == FALSE)
            {
                $Ajax->output(false, 'realm_install_error');
                return;
            }
            else
            {
                // Set as default realm if we dont have one
                if($Config->get('default_realm_id') == 0)
                {
                    // Set the new default Realm
                    $Config->set('default_realm_id', $data['id'], 'App');
                    $Config->save('App');
                }
                ($good == TRUE) ? $Ajax->output(true, 'realm_install_success') : $Ajax->output(true, 'realm_install_warning', 'warning');
            }
        }
        else
        {
            // Update the realms table
            $result = $this->DB->update('pcms_realms', $data, "`id`=".$id);
            if($result === FALSE)
            {
                $Ajax->output(false, 'realm_update_error');
                return;
            }
            else
            {
                ($good == TRUE) ? $Ajax->output(true, 'realm_update_success') : $Ajax->output(true, 'realm_update_warning', 'warning');
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: uninstall_realm()
| ---------------------------------------------------------------
|
| Removes a realm from the installed realms list
|
|
*/
    public function uninstall_realm()
    {
        // Load our config class
        $Config = load_class('Config');
        $Ajax = get_instance();
        
        // Get our realm ID and the default realm ID
        $id = $_POST['id'];
        $default = $Config->get('default_realm_id');
        
        // Run the delete though the database
        $result = $this->DB->delete('pcms_realms', '`id`='.$id.'');
        
        // If we are uninstalling the default Realm, we set a new one
        if($id == $default)
        {
            // Get the new Default Realm
            $installed = get_installed_realms();
            
            if($installed == FALSE || empty($installed))
            {
                // Set the new default Realm
                $Config->set('default_realm_id', 0, 'App');
                $Config->save('App');
            }
            else
            {
                // Set the new default Realm
                $Config->set('default_realm_id', $installed[0]['id'], 'App');
                $Config->save('App');
            }
        }
        ($result == TRUE) ? $Ajax->output(true, 'realm_uninstall_success') : $Ajax->output(false, 'realm_uninstall_failed', 'error');
    }
    
/*
| ---------------------------------------------------------------
| Method: realm_status()
| ---------------------------------------------------------------
|
| Echos the realm status of each realm is Json
|
*/
    public function realm_status()
    {
        // Load our config class
        $Config = load_class('Config');
        $Cache = $this->load->library('Cache');
        $Ajax = get_instance();
 
        // See if we have cached results
        $result = $Cache->get('ajax_realm_status');
        if($result == FALSE)
        {
            // Set to array
            $result = array();

            // If we are here, then the cache results were expired
            $Debug = load_class('Debug');
            $this->load->helper('Time');
            
            // Build our query
            $query = "SELECT `id`, `name`, `type`, `address`, `port`, `max_players` FROM `pcms_realms`";
            
            // fetch the array of realms
            $realms = $this->DB->query( $query )->fetch_array();
            if($realms == FALSE) $realms = array();
            
            // Loop through each realm, and get its status
            foreach($realms as $key => $realm)
            {

                // Dont show errors errors
                $Debug->silent_mode(true);
                $handle = @fsockopen($realm['address'], $realm['port'], $errno, $errstr, 2);
                $Debug->silent_mode(false);
                
                // Set our status var
                ($handle == FALSE) ? $status = 0 : $status = 1;
                
                // Load the wowlib for this realm
                $wowlib = $this->load->wowlib($realm['id']);

                // Build our realms return
                if($status == 1 && $wowlib != FALSE)
                {
                    // Get our realm uptime
                    $uptime = $this->realm->uptime( $realm['id'] );
                    ($uptime == FALSE) ? $uptime = 'Unavailable' : $uptime = format_time($uptime);
                    
                    // Determine population
                    $online = $wowlib->get_online_count(0);
                    $space = $realm['max_players'] - $online;
                    $percent = ($space < 0) ? 100 : $space / $realm['max_players'];
                    
                    // Get our population text
                    if($percent < 40)
                    {
                        $pop = '<span id="realm_population_low">Low</span>';
                    }
                    elseif($percent < 75)
                    {
                        $pop = '<span id="realm_population_medium">Medium</span>';
                    }
                    else
                    {
                        $pop = ($percent > 98) ? '<span id="realm_population_full">Full</span>' : '<span id="realm_population_high">High</span>';
                    }

                    // Build our realm info array
                    $result[] = array(
                        'id' => $realm['id'],
                        'name' => $realm['name'],
                        'type' => $realm['type'],
                        'status' => $status,
                        'population' => $pop,
                        'online' => $online,
                        'alliance' => $wowlib->get_online_count(1),
                        'horde' => $wowlib->get_online_count(2),
                        'uptime' => $uptime
                    );
                }
                else
                {
                    $result[] = array(
                        'id' => $realm['id'],
                        'name' => $realm['name'],
                        'type' => $realm['type'],
                        'status' => $status,
                        'population' => '<span id="realm_population_low">Low</span>',
                        'online' => 0,
                        'alliance' => 0,
                        'horde' => 0,
                        'uptime' => 'Offline'
                    );
                }
            }
            
            // Cache the results for 2 minutes
            $Cache->save('ajax_realm_status', $result, 120);
        }

        // Push the output in json format
        echo json_encode($result);
    }

/*
| ---------------------------------------------------------------
| Method: process_datatables()
| ---------------------------------------------------------------
|
| Returns an array for the DataTables JS script
|
| @Param: (Array) $aColumns - The array of DB columns to process
| @Param: (Array) $sIndexColumn - The index column such as "id"
| @Param: (Array) $sTable - The table we are query'ing
| @Param: (String) $cWhere - Additional WHERE statements
| @Param: (String or Object) $DB - Database Object or ID
| @Return (Array)
|
*/    
    public function process_datatables($aColumns, $sIndexColumn, $sTable, $cWhere = '', $DB = 'DB')
    {
        /* DB Setup */
        if(!is_object($DB)) $DB = $this->$DB;

        /* Paging */
        $sLimit = "";
        if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ". addslashes( $_POST['iDisplayStart'] ) .", ". addslashes( $_POST['iDisplayLength'] );
        }
        
        /*  Ordering */
        $sOrder = "";
        if( isset( $_POST['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for($i = 0; $i < intval($_POST['iSortingCols']); $i++)
            {
                if( $_POST[ 'bSortable_'. intval($_POST['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= "`". $aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."`". addslashes( $_POST['sSortDir_'.$i] ) .", ";
                }
            }
            
            $sOrder = substr_replace( $sOrder, "", -2 );
            if( $sOrder == "ORDER BY" ) $sOrder = "";
        }
        
        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if( isset($_POST['sSearch']) && $_POST['sSearch'] != "" )
        {
            $sWhere = "WHERE (";
            for ($i = 0; $i < count($aColumns); $i++)
            {
                $sWhere .= "`". $aColumns[$i]."` LIKE '%". addslashes( $_POST['sSearch'] ) ."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }
        
        /* Individual column filtering */
        for($i = 0; $i < count($aColumns); $i++)
        {
            if( isset($_POST['bSearchable_'.$i]) && $_POST['bSearchable_'.$i] == "true" && $_POST['sSearch_'.$i] != '' )
            {
                $sWhere .= ($sWhere == "") ? "WHERE " : " AND ";
                $sWhere .= "`".$aColumns[$i]."` LIKE '%". addslashes($_POST['sSearch_'.$i]) ."%' ";
            }
        }
        
        /* Additional where statement */
        if(!empty($cWhere))
        {
            $sWhere .= ($sWhere == "") ? "WHERE ". $cWhere : " AND ". $cWhere;
        }
        
        /* SQL queries, Get data to display */
        $columns = "`". str_replace(",``", " ", implode("`, `", $aColumns)) ."`";
        $sQuery = "SELECT SQL_CALC_FOUND_ROWS {$columns} FROM {$sTable} {$sWhere} {$sOrder} {$sLimit}";
        $rResult = $DB->query( $sQuery )->fetch_array('BOTH');
        
        /* Data set length after filtering */
        $iFilteredTotal = $DB->query( "SELECT FOUND_ROWS()" )->fetch_column();
        
        /* Total data set length */
        $iTotal = $DB->query( "SELECT COUNT(`".$sIndexColumn."`) FROM   $sTable" )->fetch_column();

        /* Output */
        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => intval($iTotal),
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
        foreach( $rResult as $aRow )
        {
            $row = array();
            for($i = 0; $i < count($aColumns); $i++)
            {
                if( $aColumns[$i] == "version" )
                {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
                }
                elseif( $aColumns[$i] != ' ' )
                {
                    /* General output */
                    $row[] = $aRow[ $aColumns[$i] ];
                }
            }
            $output['aaData'][] = $row;
        }
        
        return $output;
    }
}
// EOF