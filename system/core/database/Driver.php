<?php
/* 
| --------------------------------------------------------------
| Plexis Core
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Copyright:    Copyright (c) 2011-2012, Plexis Dev Team
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Driver
| ---------------------------------------------------------------
|
| PDO extension driver, which is passed when a new DB connection
| is made from the Database Factory
|
*/
namespace Database;

class Driver extends \PDO
{
    // Driver
    protected $driver;
    
    // The most recen query
    protected $last_query = '';

    // All sql statement that have been ran
    protected $queries = array();
    
    // Replacments for the last query
    protected $sprints;

    // Our last queries number of rows / affected rows
    protected $num_rows;

    // Queries statistics.
    protected $statistics = array(
        'total_time' => 0,
        'total_queries' => 0,
    );
    
    // result of the last query
    public $result;

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
| Creates the connection to the database using PDO
|
*/
    public function __construct($i)
    {
        // Create our DSN string
        $dsn = $i['driver'] .':host='.$i['host'] .';port='.$i['port'] .';dbname='.$i['database'];
        
        // Connect using the PDO Constructor
        try {
            parent::__construct($dsn, $i['username'], $i['password'], array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
        }
        catch (\Exception $e) {
            throw new \Exception( $e->getMessage() );
        }
    }

 
/*
| ---------------------------------------------------------------
| Function: query()
| ---------------------------------------------------------------
|
| The main method for querying the database. This method also
| benchmarks times for each query, as well as stores the query
| in the $sql array.
|
| @Param: (String) $query - The full query statement
| @Param: (Array) $sprints - An array or replacemtnts of (?)'s in the $query
| @Param: (Bool) $report_error - Trigger an error upon error?
|
*/
    public function query($query, $sprints = null, $report_error = true)
    {
        // Add query to the last query and benchmark
        $bench['query'] = $this->last_query = $query;
        
        // Set our sprints, and bindings to false
        $this->sprints = $sprints;
        
        // Prepare the statement
        $this->result = $this->prepare($query);

        // Time, and process our query
        $start = microtime(true);
        try {
            $this->result->execute($sprints);
            $failed = false;
        }
        catch (\PDOException $e) {
            if($report_error == true) $this->triggerError();
            $failed = true;
        }
        $end = microtime(true);
        
        // Get our benchmark time
        $bench['time'] = round($end - $start, 6);

        // Add the query to the list of queries
        $this->queries[] = $bench;

        // Up our statistic count
        ++$this->statistics['total_queries'];
        $this->statistics['total_time'] = ($this->statistics['total_time'] + $bench['time']);
        
        // Check for false return
        if($failed)
        {
            // Set our result to FALSE
            $this->result = false;
            $this->num_rows = 0;
        }
        else
        {
            // Get our number of rows
            $this->num_rows = $this->result->rowCount();
        }

        // Return
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: exec()
| ---------------------------------------------------------------
|
| This method is the wrapper for PDO's exec method. We are intercepting
| so we can add the query to our statistics, and catch errors
|
| @Param: (String) $query - The full query statement
| @Param: (Bool) $report_error - Trigger an error upon error?
| @Return: (Mixed) FALSE on error, otherwise nuber of rows affected
|
*/
    public function exec($query, $report_error = true)
    {
        // Add query to the last query and benchmark
        $bench['query'] = $this->last_query = $query;

        // Time our query
        $start = microtime(true);
        try {
            $result = parent::exec($query);
            $failed = false;
        }
        catch (\PDOException $e) {
            if($report_error == true) $this->triggerError();
            $failed = true;
        }
        $end = microtime(true);

        // Get our benchmark time
        $bench['time'] = round($end - $start, 6);

        // Add the query to the list of queries
        $this->queries[] = $bench;

        // Up our statistic count
        ++$this->statistics['total_queries'];
        $this->statistics['total_time'] = ($this->statistics['total_time'] + $bench['time']);
        
        // Just return an absolute false (bool) on error
        return ($failed) ? false : $result;
    }

/*
| ---------------------------------------------------------------
| Function: fetchAll()
| ---------------------------------------------------------------
|
| this method fetches a multi demensional array (multiple rows)
|   of data from the database.
|
*/
    public function fetchAll($type = 'ASSOC', $param = null)
    {
        // Make sure we dont have a false return
        if($this->result == false || $this->result == null) return false;
        
        // Get our real type if we dont already have it
        if(!is_numeric($type)) $type = $this->getFetchType($type);
        
        // Fetch the result array
        if($param !== NULL)
        {
            return $this->result->fetchAll($type, $param);
        }
        return $this->result->fetchAll($type);
    }

/*
| ---------------------------------------------------------------
| Function: fetchRow()
| ---------------------------------------------------------------
|
| this method returns just 1 row of data
|
*/
    public function fetchRow($type = 'ASSOC')
    {
        // Make sure we dont have a false return
        if($this->result == false || $this->result == null) return false;
        
        // Get our real type if we dont already have it
        if(!is_numeric($type)) $type = $this->getfetchType($type);
        
        // Fetch the result array
        return $this->result->fetch($type);
    }

/*
| ---------------------------------------------------------------
| Function: fetchColumn()
| ---------------------------------------------------------------
|
| fetchs the first column from the last array.
|
*/
    public function fetchColumn($col = 0)
    {
        // Make sure we dont have a false return
        if($this->result == false || $this->result == null) return false;
        return $this->result->fetchColumn($col);
    }

/*
| ---------------------------------------------------------------
| Function: getFetchType()
| ---------------------------------------------------------------
|
| Return the PDO fetch type
|
*/
    public function getFetchType($type)
    {
        $type = strtoupper($type);
        switch($type)
        {
            case "ASSOC": return \PDO::FETCH_ASSOC;
            case "NUM": return \PDO::FETCH_NUM;
            case "BOTH": return \PDO::FETCH_BOTH;
            case "COLUMN": return \PDO::FETCH_COLUMN;
            case "CLASS": return \PDO::FETCH_CLASS;
            case "LAZY": return \PDO::FETCH_LAZY;
            case "INTO": return \PDO::FETCH_INTO;
            case "OBJ": return \PDO::FETCH_OBJ;   
            default: return \PDO::FETCH_ASSOC;
        }
    }

/*
| ---------------------------------------------------------------
| Function: insert()
| ---------------------------------------------------------------
|
| An easy method that will insert data into a table
|
| @Param: (String) $table - The table name we are inserting into
| @Param: (String) $data - An array of "column => value"'s
| @Return: (Bool) Returns TRUE on success of FALSE on error
|
*/
    public function insert($table, $data)
    {
        // enclose the column names in grave accents
        $cols = '`' . implode('`,`', array_keys($data)) . '`';
        $values = '';

        // question marks for escaping values later on
        $count = count($data);
        for($i = 0; $i < $count; $i++)
        {
            $values .= "?, ";
        }
        
        // Remove the last comma
        $values = rtrim($values, ', ');

        // run the query
        $query = 'INSERT INTO ' . $table . '(' . $cols . ') VALUES (' . $values . ')';

        // Prepare the statment
        $this->query( $query, array_values($data) );
        
        return $this->num_rows; 
    }

/*
| ---------------------------------------------------------------
| Function: update()
| ---------------------------------------------------------------
|
| An easy method that will update data in a table
|
| @Param: (String) $table - The table name we are inserting into
| @Param: (Array) $data - An array of "column => value"'s
| @Param: (String) $where - The where statement Ex: "id = 5"
| @Return: (Bool) Returns TRUE on success of FALSE on error
|
*/
    public function update($table, $data, $where = '')
    {
        // Our string of columns
        $cols = '';
        
        // Do we have a where tp process?
        ($where != '') ? $where = ' WHERE ' . $where : '';

        // start creating the SQL string and enclose field names in `
        foreach($data as $key => $value) 
        {
            $cols .= ', `' . $key . '` = ?';
        }

        // Trim the first comma, dont worry. ltrim is really quick :)
        $cols = ltrim($cols, ', ');
        
        // Build our query
        $query = 'UPDATE ' . $table . ' SET ' . $cols . $where;

        // Execute the query
        $this->query( $query, array_values($data) );
        
        return $this->num_rows;
    }

/*
| ---------------------------------------------------------------
| Function: delete()
| ---------------------------------------------------------------
|
| An easy method that will delete data from a table
|
| @Param: (String) $table - The table name we are inserting into
| @Param: (String) $where - The where statement Ex: "id = 5"
| @Return: (Bool) Returns TRUE on success of FALSE on error
|
*/
    public function delete($table, $where = '')
    {
        // run the query
        $this->num_rows = $this->exec('DELETE FROM ' . $table . ($where != '' ? ' WHERE ' . $where : ''));

        // Return TRUE or FALSE
        return ($this->num_rows > 0) ? true : false;
    }

/*
| ---------------------------------------------------------------
| Function: num_rows()
| ---------------------------------------------------------------
|
| This method returns 1 of 2 things. A) either the number of
| affected rows during the last insert/delete/update query. Or
| B) The number of rows (count) in the result array.
|
| @Param: (Bool) $real - Setting this to TRUE will return The
|   real number of rows. This is not needed unless the last
|   query was a SELECT query.
| @Return: (Int) Returns the number of rows in the last query
|
*/
    public function numRows($real = false)
    {
        // If we are getting a real count, we need to query the
        // DB again as some DB's dont return the correct selected
        // amount of rows in a SELECT query result
        if($real)
        {
            $regex = '/^SELECT (.*) FROM (.*)$/i';
            
            // Make sure this is a SELECT statement we are dealing with
            if(preg_match($regex, $this->last_query, $output) != false) 
            {
                // Query and get our count
                $this->last_query = $bench['query'] = "SELECT COUNT(*) FROM ". $output[2];
                
                // Prepar1 the statment
                $stmt = $this->prepare( $this->last_query );

                // Time our query
                $start = microtime(true);
                try {
                    $stmt->execute( $this->sprints );
                }
                catch (\PDOException $e) { 
                    $this->triggerError();
                }
                $end = microtime(true);
                
                // Get our benchmark time
                $bench['time'] = round($end - $start, 5);

                // Add the query to the list of queries
                $this->queries[] = $bench;
            
                ++$this->statistics['count'];
                $this->statistics['time'] = ($this->statistics['time'] + $bench['time']);
                return $stmt->fetchColumn();
            }
        }
        return $this->num_rows;
    }
 
/*
| ---------------------------------------------------------------
| Function: server_info()
| ---------------------------------------------------------------
|
| Returns the DB server information
| @Return: (Array)
|
*/ 
    public function serverInfo()
    {
        return array(
            'driver' => \PDO::getAttribute( \PDO::ATTR_DRIVER_NAME ),
            'version' => \PDO::getAttribute( \PDO::ATTR_SERVER_VERSION )
        );
    }
    
/*
| ---------------------------------------------------------------
| Function: statistics()
| ---------------------------------------------------------------
|
| Returns the statistic information of this connection
| @Return: (Array)
|
*/ 
    public function statistics()
    {
        return $this->statistics;
    }
    
/*
| ---------------------------------------------------------------
| Function: get_all_queries()
| ---------------------------------------------------------------
|
| Returns an array of all queries thus far and each quesries
|   statistical data such as query time.
| @Return: (Array)
|
*/ 
    public function getAllQueries()
    {
        return $this->queries;
    }
    

/*
| ---------------------------------------------------------------
| Function: reset()
| ---------------------------------------------------------------
|
| Clears out and resets the query statistics
|
| @Return: (None)
|
*/
    public function reset()
    {
        $this->queries = array();
        $this->statistics = array(
            'total_time'  => 0,
            'total_queries' => 0
        );
    }

/*
| ---------------------------------------------------------------
| Function: __get()
| ---------------------------------------------------------------
|
| Magic method to load driver extensions
|
| @Return: (None)
|
*/    
    public function __get($name)
    {
        // Just return the extension if it exists
        if(isset($this->$name)) return $this->$name;
        
        // Create our classname
        $class = ucfirst($name);
        
        // Check for the extension, if not found, doesnt exists :O
        $file = path(SYSTEM_PATH, 'database', 'extensions', $class . '.php');
        if(!file_exists( $file ))
        {
            show_error('db_autoload_failed', array($name), E_ERROR);
            return false;
        }
        
        // Include the File
        require_once($file);
        
        // Load the class
        $class = "\\Database\\".$class;
        $this->$name = new $class($this);
        return $this->$name;
    }

/*
| ---------------------------------------------------------------
| Function: triggerError()
| ---------------------------------------------------------------
|
| Trigger a Core error using a custom error message
|
*/

    protected function triggerError() 
    {
        // Get our driver name and error information
        $errInfo = $this->result->errorInfo();
        $driver = \PDO::getAttribute( \PDO::ATTR_DRIVER_NAME );
        
        // Build our error message
        $msg  = $errInfo[2] . "<br /><br />";
        $msg .= "<b>PDO Error No:</b> ". $errInfo[0] ."<br />";
        $msg .= "<b>". ucfirst($driver) ." Error No:</b> ". $errInfo[1] ."<br />";
        $msg .= "<b>Query String: </b> ". $this->last_query ."<br />";
        show_error($msg, false, E_ERROR);
    }
}
// EOF