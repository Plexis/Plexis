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

class Database extends \PDO
{
    // The most recen query
    protected $last_query = '';
    
    // Replacments for the last query
    protected $sprints;

    // Our last queries number of rows / affected rows
    protected $num_rows;
    
    // result of the last query
    public $result;

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
| Donot construct the parent yet!
|
*/
    public function __construct(){}

/*
| ---------------------------------------------------------------
| Function: Connect()
| ---------------------------------------------------------------
|
| Creates the connection to the database using PDO
|
*/
    public function connect($i)
    {
        // Create our DSN based off our driver
        if($i['driver'] == 'sqlite')
        {
            $dsn = 'sqlite:dbname='. ROOT . DS . $i['database'];
        }
        else
        {
            $dsn = $i['driver'] .':dbname='.$i['database'] .';host='.$i['host'] .';port='.$i['port'];
        }
        
        // Try and Connect to the database
        try 
        {
            // Connect using the PDO constructer
            parent::__construct($dsn, $i['username'], $i['password'], array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
            $result = TRUE;
        }
        catch (\PDOException $e)
        {
            $result = FALSE;
        }
        return $result;
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
| @Param: $query - The full query statement
| @Param: $sprints - An array or replacemtnts of (?)'s in the $query
|
*/
    public function query($query, $sprints = NULL)
    {
        // Add to last query
        $this->last_query = $query;
        
        // Set our sprints, and bindings to false
        $this->sprints = $sprints;
        
        // Prepare the statement
        $this->result = $this->prepare($query);

        // process our query
        try {
            $this->result->execute($sprints);
        }
        catch (\PDOException $e) { 
            $this->trigger_error();
        }
        
        // Get our number of rows
        $this->num_rows = $this->result->rowCount();

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
| @Param: $query - The full query statement
|
*/
    public function exec($query)
    {
        // Add query to the last query and benchmark
        $this->last_query = $query;

        // Time our query
        try {
            $result = parent::exec($query);
        }
        catch (\PDOException $e) { 
            $result = FALSE;
        }

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
                $this->last_query = "SELECT COUNT(*) FROM ". $output[2];
                
                // Get our sprints
                $sprints = $this->sprints;
                
                // Prepar1 the statment
                $stmt = $this->prepare( $this->last_query );

                // Run the Query
                try {
                    $stmt->execute($sprints);
                }
                catch (\PDOException $e) { 
                    $this->trigger_error();
                }
                
                // Return
                return $stmt->fetchColumn();
            }
        }
        return $this->num_rows;
    }
    
/*
| ---------------------------------------------------------------
| Function: run_sql_file()
| ---------------------------------------------------------------
|
| Runs a sql file on the database
|
*/
    public function run_sql_file($file)
    {
        // Open the sql file, and add each line to an array
        $handle = @fopen($file, "r");
        if($handle) 
        {
            while(!feof($handle)) 
            {
                $queries[] = fgets($handle);
            }
            fclose($handle);
        }
        else 
        {
            return FALSE;
        }
        
        // loop through each line and process it
        foreach ($queries as $key => $aquery) 
        {
            // If the line is empty or a comment, unset it
            if (trim($aquery) == "" || strpos ($aquery, "--") === 0 || strpos ($aquery, "#") === 0) 
            {
                unset($queries[$key]);
                continue;
            }
            
            // Check to see if the query is more then 1 line
            $aquery = rtrim($aquery);
            $compare = rtrim($aquery, ";");
            if($compare != $aquery) 
            {
                $queries[$key] = $compare . "|br3ak|";
            }
        }

        // Combine the query's array into a string, 
        // and explode it back into an array seperating each query
        $queries = implode($queries);
        $queries = explode("|br3ak|", $queries);

        // Process each query
        foreach ($queries as $query) 
        {
            // Dont query if the query is empty
            if(empty($query)) continue;
            $result = $this->exec($query);
            if($result === FALSE) return FALSE;
        }
        return TRUE;
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
        show_error($msg);
    }
}
// EOF