<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author: 		Steven Wilson
| Copyright:	Copyright (c) 2011, Steven Wilson
| License: 		GNU GPL v3
|
*/
namespace System\Database;

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
| Constructer
| ---------------------------------------------------------------
|
| Creates the connection to the database using PDO
|
*/
    public function __construct($i)
    {
        // Create our DSN based off our driver
        if($i['driver'] == 'sqlite')
        {
            $dsn = 'sqlite:dbname='. $i['database'];
        }
        else
        {
            $dsn = $i['driver'] .':host='.$i['host'] .';port='.$i['port'] .';dbname='.$i['database'];
        }
        
        // Try and Connect to the database
        try 
        {
            // Connect using the PDO constructer
            parent::__construct($dsn, $i['username'], $i['password'], array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
        }
        catch (\PDOException $e)
        {
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
    public function query($query, $sprints = NULL, $report_error = TRUE)
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
            $failed = FALSE;
        }
        catch (\PDOException $e) { 
            if($report_error == TRUE) $this->trigger_error();
            $failed = TRUE;
        }
        $end = microtime(true);
        
        // Get our benchmark time
        $bench['time'] = round($end - $start, 5);

        // Add the query to the list of queries
        $this->queries[] = $bench;

        // Up our statistic count
        $this->statistics['total_queries']++;
        $this->statistics['total_time'] = ($this->statistics['total_time'] + $bench['time']);
        
        // Check for false return
        if($failed == TRUE)
        {
            // Set our result to FALSE
            $this->result = FALSE;
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
    public function exec($query, $report_error = TRUE)
    {
        // Add query to the last query and benchmark
        $bench['query'] = $this->last_query = $query;

        // Time our query
        $start = microtime(true);
        try {
            $result = parent::exec($query);
            $failed = FALSE;
        }
        catch (\PDOException $e) { 
            if($report_error == TRUE) $this->trigger_error();
            $failed = TRUE;
        }
        $end = microtime(true);

        // Get our benchmark time
        $bench['time'] = round($end - $start, 5);

        // Add the query to the list of queries
        $this->queries[] = $bench;

        // Up our statistic count
        $this->statistics['total_queries']++;
        $this->statistics['total_time'] = ($this->statistics['total_time'] + $bench['time']);
        
        // Just return an absolute false (bool) on error
        if($failed == TRUE) return FALSE;

        // Return
        return $result;
    }

/*
| ---------------------------------------------------------------
| Function: fetch_array()
| ---------------------------------------------------------------
|
| fetch_array fetches a multi demensional array (multiple rows)
|   of data from the database.
|
*/
    public function fetch_array($type = 'ASSOC', $param = NULL)
    {
        // Make sure we dont have a false return
        if($this->result == FALSE || $this->result == NULL) return FALSE;
        
        // Get our real type if we dont already have it
        if(!is_int($type))
        {
            $type = $this->get_fetch_type($type);
        }
        
        // Fetch the result array
        if($param !== NULL)
        {
            return $this->result->fetchAll($type, $param);
        }
        return $this->result->fetchAll($type);
    }

/*
| ---------------------------------------------------------------
| Function: fetch_row()
| ---------------------------------------------------------------
|
| fetch_row return just 1 row of data
|
*/
    public function fetch_row($type = 'ASSOC', $row = 0)
    {
        // Make sure we dont have a false return
        if($this->result == FALSE || $this->result == NULL) return FALSE;
        
        // Get our real type if we dont already have it
        if(!is_numeric($type))
        {
            $type = $this->get_fetch_type($type);
        }
        
        // Fetch the result array
        return $this->result->fetch($type, $row);
    }

/*
| ---------------------------------------------------------------
| Function: fetch_column()
| ---------------------------------------------------------------
|
| fetchs the first column from the last array.
|
*/
    public function fetch_column($col = 0)
    {
        // Make sure we dont have a false return
        if($this->result == FALSE || $this->result == NULL) return FALSE;
        return $this->result->fetchColumn($col);
    }

/*
| ---------------------------------------------------------------
| Function: get_fetch_type()
| ---------------------------------------------------------------
|
| Return the PDO fetch type
|
*/
    public function get_fetch_type($type)
    {
        $type = strtoupper($type);
        switch($type)
        {
            case "ASSOC":
                return \PDO::FETCH_ASSOC;

            case "NUM":
                return \PDO::FETCH_NUM;

            case "BOTH":
                return \PDO::FETCH_BOTH;

            case "COLUMN":
                return \PDO::FETCH_COLUMN;

            case "CLASS":
                return \PDO::FETCH_CLASS;

            case "LAZY":
                return \PDO::FETCH_LAZY;

            case "INTO":
                return \PDO::FETCH_INTO;

            case "OBJ":
                return \PDO::FETCH_OBJ;
                
            default:
                return \PDO::FETCH_ASSOC;
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
        if($this->num_rows > 0)
        {
            return TRUE;
        }
        return FALSE;
    }

/*
| ---------------------------------------------------------------
| Function: last_insert_id()
| ---------------------------------------------------------------
|
| The equivelant to mysql_insert_id(); This functions get the last
| primary key from a previous insert
|
| @Return: (Int) Returns the insert id of the last insert
|
*/
    public function last_insert_id($colname = NULL)
    {
        return $this->lastInsertId($colname);
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
    public function num_rows($real = FALSE)
    {
        // If we are getting a real count, we need to query the
        // DB again as some DB's dont return the correct selected
        // amount of rows in a SELECT query result
        if($real == TRUE)
        {
            $regex = '/^SELECT (.*) FROM (.*)$/i';
            
            // Make sure this is a SELECT statement we are dealing with
            if(preg_match($regex, $this->last_query, $output) != FALSE) 
            { 
                // Query and get our count
                $this->last_query = $bench['query'] = "SELECT COUNT(*) FROM ". $output[2];
                
                // Get our sprints
                $sprints = $this->sprints;
                
                // Prepar1 the statment
                $stmt = $this->prepare( $this->last_query );

                // Time our query
                $start = microtime(true);
                try {
                    $stmt->execute($sprints);
                }
                catch (\PDOException $e) { 
                    $this->trigger_error();
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
    public function server_info()
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
    public function get_all_queries()
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
        if(isset($this->$name))
        {
            return $this->$name;
        }
        
        // Create our classname
        $class = ucfirst($name);
        
        // Check for the extension
        if(file_exists(APP_PATH. DS . 'database' . DS . 'extensions' . DS . $class .'.php')) 
        {
            require_once(APP_PATH. DS . 'database' . DS . 'extensions' . DS . $class .'.php');
            $prefix = "\\Application";
        }
        elseif(file_exists(SYSTEM_PATH. DS . 'database' . DS . 'extensions' . DS . $class .'.php'))
        {
            require_once(SYSTEM_PATH. DS . 'database' . DS . 'extensions' . DS . $class .'.php');
            $prefix = "\\System";
        }
        else
        {
            // Extension doesnt exists :O
            show_error('db_autoload_failed', array($name), E_ERROR);
            return FALSE;
        }
        
        // Load the class
        $class = $prefix ."\\Database\\Extensions\\".$class;
        $this->$name = new $class($this);
        return $this->$name;
    }

/*
| ---------------------------------------------------------------
| Function: trigger_error()
| ---------------------------------------------------------------
|
| Trigger a Core error using a custom error message
|
*/

    protected function trigger_error() 
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