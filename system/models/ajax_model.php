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
class Ajax_Model extends Core\Model 
{

/*
| ---------------------------------------------------------------
| Constructor
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
        $this->user = $Ajax->User->data;
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
        $Account = $this->realm->fetchAccount($id);

        // Update password if there was a change
        if(!empty($password[0]) && !empty($password[1]))
        {
            if( $password[0] == $password[1] )
            {
                $changes = TRUE;
                $result = $Account->setPassword($password[0]);
                if( $result === FALSE )
                {
                    $Ajax->output(false, 'account_update_error');
                    return;
                }
                
                // Fire the change password event
                load_class('Events')->trigger('password_change', array($id, $password[0]));
            }
            else
            {
                $Ajax->output(false, 'account_update_error');
                return;
            }
        }	
        
        // Update expansion
        if($expansion != $Account->getExpansion())
        {
            $changes = TRUE;
            $result = $Account->setExpansion($expansion);
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
                
                // Fire change email event if changed
                if($key == 'email')
                {
                    $Account->setEmail($value);
                    load_class('Events')->trigger('email_change', array($id, $user['email'], $value));
                }
            }
        }
        
        // Save the account
        if($changes && !$Account->save())
        {
            $Ajax->output(false, 'account_update_error');
            return;
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
        $Ajax = get_instance();

        // Get our posted information
        $id = ( $action == 'manual-install' ) ? 0 : $_POST['id'];
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
            $result = $this->realm->getRealmlist();
            $installed = get_installed_realms();
            
            if( !empty($result) )
            {
                $highest = end($result);
                if( empty($installed) )
                {
                    $id = ($highest->getId() + 1);
                }
                else
                {
                    $high2 = end($installed);
                    ($highest->getId() > $high2['id']) ? $id = ($highest->getId() + 1) : $id = $high2['id'] + 1;
                }
            }
            else
            {
                if( empty( $installed ) )
                {
                    $id = 1;
                }
                else
                {
                    $highest = end( $installed );
                    $id = $highest['id'] + 1;
                }
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
            // Set the result to array, and load time helper
            $result = array();
            $this->load->helper('Time');
            
            // Fetch the array of realms
            $query = "SELECT `id`, `name`, `type`, `address`, `port`, `max_players` FROM `pcms_realms`";
            $realms = $this->DB->query( $query )->fetchAll();
            if($realms == FALSE) $realms = array();
            
            // Loop through each realm, and get its status
            foreach($realms as $key => $realm)
            {
                // Dont show warning produced by fsockopen
                \Debug::silent_mode(true);
                $handle = fsockopen($realm['address'], $realm['port'], $errno, $errstr, 2);
                \Debug::silent_mode(false);
                
                // Set our status var
                ($handle == FALSE) ? $status = 0 : $status = 1;
                
                // Load the wowlib for this realm
                $wowlib = $this->load->wowlib($realm['id']);

                // Build our realms return
                if($status == 1 && is_object($wowlib))
                {
                    // Get our realm uptime
                    $uptime = $this->realm->uptime( $realm['id'] );
                    ($uptime == FALSE) ? $uptime = 'Unavailable' : $uptime = format_time($uptime);
                    
                    // Determine population
                    $ally = $wowlib->characters->getOnlineCount(1);
                    $horde = $wowlib->characters->getOnlineCount(2);
                    $online = $ally + $horde;
                    $space = $realm['max_players'] - $online;
                    $percent = ($space < 0) ? 100 : $space / $realm['max_players'];
                    
                    // Get our population text
                    if($percent < 40)
                        $pop = '<span id="realm_population_low">Low</span>';
                    elseif($percent < 75)
                        $pop = '<span id="realm_population_medium">Medium</span>';
                    else
                        $pop = ($percent > 98) ? '<span id="realm_population_full">Full</span>' : '<span id="realm_population_high">High</span>';

                    // Build our realm info array
                    $result[] = array(
                        'success' => true,
                        'id' => $realm['id'],
                        'name' => $realm['name'],
                        'type' => $realm['type'],
                        'status' => $status,
                        'population' => $pop,
                        'online' => $online,
                        'alliance' => $ally,
                        'horde' => $horde,
                        'uptime' => $uptime
                    );
                }
                else
                {
                    $result[] = array(
                        'success' => true,
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
        
        /* Lighten the load by defining this early */
        $aColumnCount = count($aColumns);

        /* Paging */
        $sLimit = "";
        if( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ". intval( $_POST['iDisplayStart'] ) .", ". intval( $_POST['iDisplayLength'] );
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
                    $sortDir = (strcasecmp($_POST['sSortDir_'.$i], 'ASC') == 0) ? 'ASC' : 'DESC';
                    $sOrder .= "`". $aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."` ". $sortDir .", ";
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
            for ($i = 0; $i < $aColumnCount; $i++)
            {
                $sWhere .= "`". $aColumns[$i]."` LIKE :search OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }
        
        /* Individual column filtering */
        for($i = 0; $i < $aColumnCount; $i++)
        {
            if( isset($_POST['bSearchable_'.$i]) && $_POST['bSearchable_'.$i] == "true" && $_POST['sSearch_'.$i] != '' )
            {
                $sWhere .= ($sWhere == "") ? "WHERE " : " AND ";
                $sWhere .= "`".$aColumns[$i]."` LIKE :search".$i." ";
            }
        }
        
        /* Additional where statement */
        if(!empty($cWhere))
        {
            $sWhere .= ($sWhere == "") ? "WHERE ". $cWhere : " AND ". $cWhere;
        }
        
        /* SQL queries, Get data to display */
        $sQuery = "SELECT `".str_replace(" , ", " ", implode("`, `", $aColumns))."` FROM `".$sTable."` ".$sWhere." ".$sOrder." ".$sLimit;
        $Statement = $DB->prepare($sQuery);
        
        // Bind parameters
        if( isset($_POST['sSearch']) && $_POST['sSearch'] != "" ) 
        {
            $Statement->bindValue(':search', '%'.$_POST['sSearch'].'%', \PDO::PARAM_STR);
        }
        for( $i=0; $i < $aColumnCount; $i++ ) 
        {
            if( isset($_POST['bSearchable_'.$i]) && $_POST['bSearchable_'.$i] == "true" && $_POST['sSearch_'.$i] != '' ) 
            {
                $Statement->bindValue(':search'.$i, '%'.$_POST['sSearch_'.$i].'%', \PDO::PARAM_STR);
            }
        }
        
        // Execute our statement
        $Statement->execute();
        $rResult = $Statement->fetchAll();
        
        /* Total data set length */
        $iTotal = $DB->query( "SELECT COUNT(`".$sIndexColumn."`) FROM   $sTable" )->fetchColumn();

        /* Output */
        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => intval($iTotal),
            "iTotalDisplayRecords" => intval(count($rResult)),
            "aaData" => array()
        );
        
        // Now add each row to the aaData
        foreach( $rResult as $aRow )
        {
            $row = array();
            for($i = 0; $i < $aColumnCount; $i++)
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