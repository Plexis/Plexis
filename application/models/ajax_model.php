<?php
class Ajax_Model extends System\Core\Model 
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
        // Init DB
        $this->DB = $this->load->database( $dB_key );

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
        $rResult = $this->DB->query( $sQuery )->fetch_array('BOTH');
        
        /* Data set length after filtering */
        $iFilteredTotal = $this->DB->query( "SELECT FOUND_ROWS()" )->fetch_column();
        
        /* Total data set length */
        $iTotal = $this->DB->query( "SELECT COUNT(".$sIndexColumn.") FROM   $sTable" )->fetch_column();
        
        
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
}
// EOF