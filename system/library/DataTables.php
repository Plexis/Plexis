<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Library/DataTables.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    DataTables
 */
namespace Library;

/**
 * A jQuery Datatables server side record class for PDO database records
 *
 * @author      Steven Wilson 
 * @package     Library
 */
class DataTables
{
    /**
     * An array or $_POST or $_GET data for proccessing
     * @var mixed[]
     */
    protected $data;
    
    /**
     * The dataprop to be returned.
     * @see http://datatables.net/usage/options#sAjaxDataProp
     * @var string
     */
    protected $dataProp = 'aaData';
    
    /**
     * Class Contructor.
     *
     * @param string $method The method used by datatables (GET or POST)
     *
     * @throws \Exception Thrown if no datatabes request was made.
     */
    public function __construct($method = 'GET') 
    {
        // Get our data
        $this->data = (strtoupper($method) == 'POST') ? $_POST : $_GET;
        if(!isset($this->data['sEcho']))
           throw new \Exception('No datatables data found in request.');
    }
    
    /**
     * Sets the DataProp or array key of rows expected from DataTables.
     *
     * @see http://datatables.net/usage/options#sAjaxDataProp
     *
     * @param string $prop The dataprop
     *
     * @return void
     */
    public function setDataProp($prop)
    {
        $this->dataProp = $prop;
    }
    
    /**
     * Generates all the data, based on the rows added, for datatables to read
     * off of. The data will still need to be encoded with json_encode()
     *
     * @param string[] $aColumns The array of DB columns to process
     * @param string[] $sIndexColumn The index column such as "id"
     * @param string $sTable The table we are query'ing
     * @param string $cWhere Additional WHERE statements
     * @param \Database\Driver $DB The PDO Database Object
     *
     * @return mixed[]
     */
    public function generate($aColumns, $sIndexColumn, $sTable, $cWhere, $DB) 
    {
        // Make sure we have the correct number of columns
        $aColumnCount = count($aColumns);
        if($aColumnCount > $this->data['iColumns'])
            throw new \Exception('Not enough rows', 1);
        elseif($aColumnCount < $this->data['iColumns'])
            throw new \Exception('Too many rows passed', 2);

        /* Paging */
        $sLimit = "";
        if( isset( $this->data['iDisplayStart'] ) && $this->data['iDisplayLength'] != '-1' )
            $sLimit = "LIMIT ". intval( $this->data['iDisplayStart'] ) .", ". intval( $this->data['iDisplayLength'] );
        
        /*  Ordering */
        $sOrder = "";
        if( isset( $this->data['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for($i = 0; $i < intval($this->data['iSortingCols']); $i++)
            {
                if( $this->data[ 'bSortable_'. intval($this->data['iSortCol_'.$i]) ] == "true" )
                {
                    $sortDir = (strcasecmp($this->data['sSortDir_'.$i], 'ASC') == 0) ? 'ASC' : 'DESC';
                    $sOrder .= "`". $aColumns[ intval( $this->data['iSortCol_'.$i] ) ]."` ". $sortDir .", ";
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
        if( isset($this->data['sSearch']) && $this->data['sSearch'] != "" )
        {
            $sWhere = "WHERE (";
            for ($i = 0; $i < $aColumnCount; $i++)
                $sWhere .= "`". $aColumns[$i]."` LIKE :search OR ";
            
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }
        
        /* Individual column filtering */
        for($i = 0; $i < $aColumnCount; $i++)
        {
            if( isset($this->data['bSearchable_'.$i]) && $this->data['bSearchable_'.$i] == "true" && $this->data['sSearch_'.$i] != '' )
            {
                $sWhere .= ($sWhere == "") ? "WHERE " : " AND ";
                $sWhere .= "`".$aColumns[$i]."` LIKE :search".$i." ";
            }
        }
        
        /* Additional where statement */
        if(!empty($cWhere))
            $sWhere .= ($sWhere == "") ? "WHERE ". $cWhere : " AND ". $cWhere;
        
        /* SQL queries, Get data to display */
        $sQuery = "SELECT `".str_replace(" , ", " ", implode("`, `", $aColumns))."` FROM `".$sTable."` ".$sWhere." ".$sOrder." ".$sLimit;
        $Statement = $DB->prepare($sQuery);
        
        // Bind parameters
        if( isset($this->data['sSearch']) && $this->data['sSearch'] != "" ) 
            $Statement->bindValue(':search', '%'.$this->data['sSearch'].'%', \PDO::PARAM_STR);
        
        for( $i=0; $i < $aColumnCount; $i++ ) 
        {
            if( isset($this->data['bSearchable_'.$i]) && $this->data['bSearchable_'.$i] == "true" && $this->data['sSearch_'.$i] != '' ) 
                $Statement->bindValue(':search'.$i, '%'.$this->data['sSearch_'.$i].'%', \PDO::PARAM_STR);
            
        }
        
        // Execute our statement
        $Statement->execute();
        $rResult = $Statement->fetchAll();
        
        /* Total data set length */
        $iTotal = $DB->query( "SELECT COUNT(`".$sIndexColumn."`) FROM `$sTable`" )->fetchColumn();

        /* Output */
        $output = array(
            "sEcho" => intval($this->data['sEcho']),
            "iTotalRecords" => intval($iTotal),
            "iTotalDisplayRecords" => intval(count($rResult)),
            "{$this->dataProp}" => array()
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
            $output["{$this->dataProp}"][] = $row;
        }
        
        return $output;
    }
}