<?php
class Ajax_Model extends Application\Core\Model 
{

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    function __construct()
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
| Method: process()
| ---------------------------------------------------------------
|
| Returns an array for the DataTables JS script
|
| @Param: (Array) $aColumns - The array of DB columns to process
| @Param: (Array) $sIndexColumn - The index column such as "id"
| @Param: (Array) $sTable - The table we are query'ing
| @Return (Array)
|
*/    
    public function process_datatables($aColumns, $sIndexColumn, $sTable, $dB_key = 'DB')
    {
        /* 
         * Paging
         */
        $sLimit = "";
        if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".addslashes( $_POST['iDisplayStart'] ).", ".
                addslashes( $_POST['iDisplayLength'] );
        }
        
        
        /*
         * Ordering
         */
        if ( isset( $_POST['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ )
            {
                if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."
                        ".addslashes( $_POST['sSortDir_'.$i] ) .", ";
                }
            }
            
            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }
        
        
        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if ( $_POST['sSearch'] != "" )
        {
            $sWhere = "WHERE (";
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                $sWhere .= $aColumns[$i]." LIKE '%".addslashes( $_POST['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }
        
        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( $_POST['bSearchable_'.$i] == "true" && $_POST['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".addslashes($_POST['sSearch_'.$i])."%' ";
            }
        }
        
        
        /*
         * SQL queries
         * Get data to display
         */
        $columns = str_replace(" , ", " ", implode(", ", $aColumns));
        $sQuery = "SELECT SQL_CALC_FOUND_ROWS {$columns} FROM {$sTable} {$sWhere} {$sOrder} {$sLimit}";
        $rResult = $this->$dB_key->query( $sQuery )->fetch_array('BOTH');
        
        /* Data set length after filtering */
        $iFilteredTotal = $this->$dB_key->query( "SELECT FOUND_ROWS()" )->fetch_column();
        
        /* Total data set length */
        $iTotal = $this->$dB_key->query( "SELECT COUNT(".$sIndexColumn.") FROM   $sTable" )->fetch_column();
        
        
        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
        foreach( $rResult as $aRow )
        {
            $row = array();
            for ( $i=0; $i < count($aColumns); $i++ )
            {
                if ( $aColumns[$i] == "version" )
                {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
                }
                else if ( $aColumns[$i] != ' ' )
                {
                    /* General output */
                    $row[] = $aRow[ $aColumns[$i] ];
                }
            }
            $output['aaData'][] = $row;
        }
        
        return $output;
    }
    
/*
| ---------------------------------------------------------------
| Method: process()
| ---------------------------------------------------------------
|
| Returns an array for the DataTables JS script
|
| @Param: (Array) $aColumns - The array of DB columns to process
| @Param: (Array) $sIndexColumn - The index column such as "id"
| @Param: (Array) $sTable - The table we are query'ing
| @Return (Array)
|
*/    
    public function get_characters_online($aColumns, $sIndexColumn, $sTable, $DB)
    {
        /* 
         * Paging
         */
        $sLimit = "";
        if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' )
        {
            if(is_numeric($_POST['iDisplayStart']) && is_numeric($_POST['iDisplayLength']))
            {
                $sLimit = "LIMIT ".addslashes( $_POST['iDisplayStart'] ).", ". addslashes( $_POST['iDisplayLength'] );
            }
        }
        
        
        /*
         * Ordering
         */
         $sOrder = "";
        if ( isset( $_POST['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ )
            {
                if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_POST['iSortCol_'.$i] ) ]." ".addslashes( $_POST['sSortDir_'.$i] ) .", ";
                }
            }
            
            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }
        
        
        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if ( isset($_POST['sSearch']) &&  $_POST['sSearch'] != "" )
        {
            $sWhere = "WHERE (";
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                $sWhere .= $aColumns[$i]." LIKE '%".addslashes( $_POST['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }
        
        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_POST['bSearchable_'.$i]) && $_POST['bSearchable_'.$i] == "true" && $_POST['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".addslashes($_POST['sSearch_'.$i])."%' ";
            }
        }
        
        // == EXTRA characters online processing! == //
        if($sWhere == '')
        {
            $sWhere = ' WHERE `online`=1';
        }
        else
        {
            $sWhere = ' AND `online`=1';
        }
        
        
        /*
         * SQL queries
         * Get data to display
         */
        $columns = str_replace(" , ", " ", implode(", ", $aColumns));
        $sQuery = "SELECT SQL_CALC_FOUND_ROWS {$columns} FROM {$sTable} {$sWhere} {$sOrder} {$sLimit}";
        $rResult = $DB->query( $sQuery )->fetch_array('BOTH');
        
        /* Data set length after filtering */
        $iFilteredTotal = $DB->query( "SELECT FOUND_ROWS()" )->fetch_column();
        
        /* Total data set length */
        $iTotal = $DB->query( "SELECT COUNT(".$sIndexColumn.") FROM   $sTable" )->fetch_column();
        
        
        /*
         * Output
         */
         $sEcho = (isset($_POST['sEcho'])) ? $_POST['sEcho'] : 1;
        $output = array(
            "sEcho" => intval($sEcho),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
        foreach( $rResult as $aRow )
        {
            $row = array();
            for ( $i=0; $i < count($aColumns); $i++ )
            {
                if ( $aColumns[$i] == "version" )
                {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
                }
                else if ( $aColumns[$i] != ' ' )
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