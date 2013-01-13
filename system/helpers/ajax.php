<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Helpers/Ajax.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @author      Plexis Dev Team
 * @package     Helpers
 * @subpackage  Ajax
 */
 
/**
 * Formats an array into a jQuery.datatables format
 *
 * @param array[] $data An array of mixed array, containing the data
 *   to format.
 * @param int $totalRecords Total records, before filtering (i.e. the 
 *   total number of records in the database)
 * @param int $totalDisplayRecords Total records, after filtering 
 *   (i.e. the total number of records after filtering has been applied - 
 *   not just the number of records being returned in this result set)
 *
 * @return string Returns the correctly formatted json_encoded array.
 */
    function dataTablesFormat( array $data, $totalRecords = false, $totalDisplayRecords = false )
    {
        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => ($totalRecords === false) 
                ? sizeof($data) 
                : $totalRecords,
            "iTotalDisplayRecords" => ($totalDisplayRecords === false) 
                ? sizeof($data) 
                : $totalDisplayRecords,
            "aaData" => array()
        );
        
        // Load each module
        foreach($data as $key => $mod)
        {
            $output['aaData'][] = array_values($mod);
        }
        
        return json_encode($output);
    }