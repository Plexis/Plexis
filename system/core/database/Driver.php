<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Database/Driver.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Driver
 */
namespace Database;

/**
 * PDO extension driver
 *
 * This class is returned from the Database::Connect() method
 * @see \Core\Database::Connect()
 *
 * @author      Steven Wilson 
 * @package     Database
 */
class Driver extends \PDO
{
    /**
     * The PDO object
     * @var \PDO Object
     */
    protected $driver;
    
    /**
     * The last query string
     * @var string
     */
    protected $last_query = '';
    
    /**
     * All sql statements that have been ran
     * @var array[]
     */
    protected $queries = array();
    
    /**
     * Replacements for the last query
     * @var mixed[]
     */
    protected $sprints;
    
    /**
     * Our last queries number of rows / affected rows
     * @var int
     */
    protected $num_rows;
    
    /**
     * Queries statistics.
     * @var int[]
     */
    protected $statistics = array(
        'total_time' => 0,
        'total_queries' => 0,
    );
    
    /**
     * The result of the last query
     */
    public $result;
    
    /**
     * Creates the connection to the database using PDO
     *
     * @param string[] $i The database connection info array
     * @return void
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
    
    /**
     * Main method for querying the database. This method also
     * benchmarks times for each query, as well as stores the query
     * in the $sql array.
     *
     * @param string $query The query to run
     * @param mixed[] $sprints An array or replacemtnts of (?)'s in the $query
     * @param bool $report_error Trigger an error upon error?
     * @return object Returns this object
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
    
    /**
     * Wrapper for PDO's exec method. We are intercepting
     * so we can add the query to our statistics, and catch errors
     *
     * @param string $query The query to run
     * @param bool $report_error Trigger an error upon error?
     * @return int|bool Returns false on error, or the number of rows affected
     *   on success.
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
    
    /**
     * Fetches a multi demensional array (multiple rows) of data from the database.
     *
     * @param string $type The PDO array type to return
     * @param string $param
     * @return mixed[]|bool Returns false if there are no rows to return, or
     *   an array of rows on success
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
    
    /**
     * Fetches an array of columns from the database.
     *
     * @param string $type The PDO array type to return
     * @return mixed[]|bool Returns false if there was no result, or
     *   an array of columns on success
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
    
    /**
     * Fetches a column from the last query result
     *
     * @param int $col The column index id
     * @return mixed|bool Returns false if there was no result, or
     *   the value of the column
     */
    public function fetchColumn($col = 0)
    {
        // Make sure we dont have a false return
        if($this->result == false || $this->result == null) return false;
        return $this->result->fetchColumn($col);
    }
    
    /**
     * Return the PDO fetch type
     *
     * @param string $type The PDO array type to return
     * @return int The PDO fetch type ID
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
    
    /**
     * An easy method that will insert data into a table
     *
     * @param string $table The table name we are inserting into
     * @param mixed[] $data An array of "column => value"'s
     * @return bool Returns TRUE on success of FALSE on error
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
    
    /**
     * An easy method that will update an existing row in a table
     *
     * @param string $table The table name we are updating
     * @param mixed[] $data An array of "column => value"'s
     * @param string $where The where statement Ex: "id = 5"
     * @return bool Returns TRUE on success of FALSE on error
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
    
    /**
     * An easy method that will delete data from a table
     *
     * @param string $table The table name we are updating
     * @param string $where The where statement Ex: "id = 5"
     * @return bool Returns TRUE on success of FALSE on error
     */
    public function delete($table, $where = '')
    {
        // run the query
        $this->num_rows = $this->exec('DELETE FROM ' . $table . ($where != '' ? ' WHERE ' . $where : ''));

        // Return TRUE or FALSE
        return ($this->num_rows > 0) ? true : false;
    }
    
    /**
     * Returns the number of rows affected, or number of rows in the result.
     *
     * This method returns 1 of 2 things. A) either the number of
     * affected rows during the last insert/delete/update query. Or
     * B) The number of rows (count) in the result array.
     *
     * @param bool $real - Setting this to TRUE will return The
     *   real number of rows. This is not needed unless the last
     *   query was a SELECT query, and you are NOT using the mysql
     *   driver.
     * @return int Returns the number of rows in the last query
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
    
    /**
     * Returns the DB server information
     *
     * @return string[] Returns the driver, and database server version
     */
    public function serverInfo()
    {
        return array(
            'driver' => \PDO::getAttribute( \PDO::ATTR_DRIVER_NAME ),
            'version' => \PDO::getAttribute( \PDO::ATTR_SERVER_VERSION )
        );
    }
    
    /**
     * Returns the statistic information of this connection
     *
     * @return string[] Returns the total query time for all queries, and
     *   total number of queries ran on this connection
     */
    public function statistics()
    {
        return $this->statistics;
    }
    
    /**
     * Returns an array of all queries thus far and each queries
     * statistical data such as query time.
     *
     * @return array[]
     */
    public function getAllQueries()
    {
        return $this->queries;
    }
    
    /**
     * Clears out and resets the query statistics
     *
     * @return void
     */
    public function reset()
    {
        $this->queries = array();
        $this->statistics = array(
            'total_time'  => 0,
            'total_queries' => 0
        );
    }
    
    /**
     * Magic method to load driver extensions
     *
     * @param string $name Name of the extension we are searching for.
     * @return object|bool Returns false if the extension class
     *   doesnt exist
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
    
    /**
     * Triggers a database error
     *
     * @return void
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
        \Core\ErrorHandler::TriggerError(E_ERROR, $msg, __FILE__, 0);
    }
}
// EOF