<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Library/DataTablesArray.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    DataTablesArray
 */
namespace Library;

/**
 * A jQuery Datatables server side record class for PHP arrays
 *
 * @author      Steven Wilson 
 * @package     Library
 */
class DataTablesArray
{
    /**
     * An array or rows and columns for output
     * @var array[]
     */
    protected $rows = array();
    
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
     * @param array[] $data An array of (Row => array of column values) to
     *   add to the list.
     *
     * @throws \Exception Thrown if no datatabes request was made.
     */
    public function __construct($method = 'GET', $data = array()) 
    {
        // Get our data
        $this->data = (strtoupper($method) == 'POST') ? $_POST : $_GET;
        if(!isset($this->data['sEcho']))
           throw new \Exception('No datatables data found in request.');
        
        // Rows can be passed in the constructor
        if(!empty($data))
        {
            foreach($data as $row)
                $this->addRow($row);
        }
    }
    
    /**
     * Adds a row to the list of rows.
     *
     * Each parameter passed to this function is a new column
     * within the row. You may also put 1 variable as an array of rows.
     *
     * @throws \Exception Thrown if the amount of column passed does not
     *   equal the amount of columns expected for each row.
     *
     * @return void
     */
    public function addRow() 
    {
        // Each argument is a column, or if the first is an array, those are columns
        $cols = func_get_args();
        if(is_array($cols[0]))
            $cols = $cols[0];
        
        // Make sure we have the correct number of columns
        $size = sizeof($cols);
        if($size > $this->data['iColumns'])
            throw new \Exception('Not enough rows', 1);
        elseif($size < $this->data['iColumns'])
            throw new \Exception('Too many rows passed', 2);
            
        // Add the row
        $this->rows[] = $cols;
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
     * @return mixed[]
     */
    public function generate() 
    {
        // Total records count
        $totalRecords = sizeof($this->rows);
        
        // Format array
        $rows = $this->buildData();
        
        // Prepare output
        return array(
            "sEcho" => intval($this->data['sEcho']),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => sizeof($rows),
            "{$this->dataProp}" => array_slice($rows, $this->data['iDisplayStart'], $this->data['iDisplayLength'])
        );
    }
    
    /**
     * Takes the added rows, applies filtering and sorting, and returns the
     * proper requested data for datatables.
     *
     * @return array[]
     */
    protected function buildData()
    {
        // Make sure we have at least 1 row
        if(sizeof($this->rows) < 1) 
            return $this->rows;
        
        // Filtering
        if( isset($this->data['sSearch']) && $this->data['sSearch'] != "" )
        {
            $rows = array();
            $cols = intval($this->data['iColumns']);
            foreach($this->rows as $row)
            {
                for($i = 0; $i < $cols; $i++)
                {
                    if(isset($this->data['bSearchable_'. $i]) && $this->data['bSearchable_'. $i] == 'true')
                    {
                        if(preg_match('~('. preg_quote($this->data['sSearch']) .')~i', $row[$i]))
                        {
                            $rows[] = $row;
                            break;
                        }
                    }
                }
            }
        }
        else
            $rows = $this->rows;
        
        // Sort array
        if(isset($this->data['iSortingCols']) && intval($this->data['iSortingCols']) > 0)
        {
            $new_array = array();
            $sortable_array = array();
            $sortBy = isset($this->data['iSortCol_0']) ? $this->data['iSortCol_0'] : 0;
            
            foreach ($rows as $k => $v) 
            {
                if (is_array($v)) 
                {
                    foreach ($v as $k2 => $v2) 
                    {
                        if ($k2 == $sortBy)
                            $sortable_array[$k] = $v2;
                    }
                } 
                else
                    $sortable_array[$k] = $v;
            }
            
            // Sort the sortable array
            switch ($this->data['sSortDir_0']) 
            {
                case 'asc':
                    asort($sortable_array);
                    break;
                case 'desc':
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v)
                $new_array[$k] = $rows[$k];
                
            return $new_array;
        }
        
        return $rows;
    }
}