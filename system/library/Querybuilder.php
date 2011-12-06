<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
*/
namespace System\Library;

class Querybuilder
{
    // Our Sql Statement
    public $sql;

    // Columns and Vaules of those columns for queries
    protected $columns = array(); 
    protected $values  = array();

/*
| ---------------------------------------------------------------
| Function: select()
| ---------------------------------------------------------------
|
| select is used to initiate a SELECT query
|
| @Param: $data - the columns being selected
|
*/
    public function select($data = '*') 
    {	
        // Empty out the old junk
        $this->clear();
        
        // Process our columns, if array, we need to implode to a string
        if(is_array($data))
        {
            if(count($data) > 1)
            {
                $this->sql = "SELECT ". $this->clean( implode(', ', $data) );
            }
            else
            {
                $this->sql = "SELECT ". $this->clean($data[0]);
            }
        }
        else
        {
            $this->sql = "SELECT ". $this->clean($data);
        }
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: max()
| ---------------------------------------------------------------
|
| max is used to initiate a SELECT MAX($col) query
|
| @Param: $col - the column being selected
| @Param: $as - The array variable key would like the count returned 
|   as in the query result.
|
*/
    public function max($col = '*', $as = NULL) 
    {
        $col = $this->clean($col);
        
        // Empty out the old junk
        $this->clear();
        
        // Build our sql statement
        $as = ($as !== NULL) ? " AS ".$as : "";
        $this->sql = "SELECT MAX(". $col .")". $as;
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: min()
| ---------------------------------------------------------------
|
| min is used to initiate a SELECT MIN($col) query
|
| @Param: $col - the column being selected
| @Param: $as - The array variable key would like the count returned 
|   as in the query result.
|
*/
    public function min($col = '*', $as = NULL) 
    {
        $col = $this->clean($col);
        
        // Empty out the old junk
        $this->clear();
        
        // Build our sql statement
        $as = ($as !== NULL) ? " AS ".$as : "";
        $this->sql = "SELECT MIN(". $col .")". $as;
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: avg()
| ---------------------------------------------------------------
|
| avg is used to initiate a SELECT AVG($col) query
|
| @Param: $col - the column being selected
| @Param: $as - The array variable key would like the count returned 
|   as in the query result.
|
*/
    public function avg($col = '*', $as = NULL) 
    {
        $col = $this->clean($col);
        
        // Empty out the old junk
        $this->clear();
        
        // Build our sql statement
        $as = ($as !== NULL) ? " AS ".$as : "";
        $this->sql = "SELECT AVG(". $col .")". $as;
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: sum()
| ---------------------------------------------------------------
|
| sum is used to initiate a SELECT SUM($col) query
|
| @Param: $col - the column being selected
| @Param: $as - The array variable key would like the count returned 
|   as in the query result.
|
*/
    public function sum($col = '*', $as = NULL) 
    {
        $col = $this->clean($col);
        
        // Empty out the old junk
        $this->clear();
        
        // Build our sql statement
        $as = ($as !== NULL) ? " AS ".$as : "";
        $this->sql = "SELECT SUM(". $col .")". $as;
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: count()
| ---------------------------------------------------------------
|
| count is used to initiate a SELECT COUNT($col) query
|
| @Param: $col - the column being selected
| @Param: $as - The array variable key would like the count returned 
|   as in the query result.
|
*/
    public function count($col = '*', $as = NULL) 
    {
        $col = $this->clean($col);
        
        // Empty out the old junk
        $this->clear();
        
        // Build our sql statement
        $as = ($as !== NULL) ? " AS ".$as : "";
        $this->sql = "SELECT COUNT(". $col .")". $as;
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: insert()
| ---------------------------------------------------------------
|
| Insert is used to initiate an INSERT query
|
| @Param: $table - the table we are inserting into
| @Param: $data - an array of ( column => value )
|
*/
    public function insert($table, $data) 
    {
        // Empty out the old junk
        $this->clear();
        
        // Define our table
        $this->table = $this->clean($table);
        
        // Make sure our data is in an array format
        if(!is_array($data))
        {
            show_error(2, 'non_array', array('data', 'Querybuilder::insert'));
            $data = array();
        }
        
        // Loop through, and seperate the array into 2 arrays
        foreach($data as $key => $value)
        {
            // Check to see if the key is numeric, if not, then escape it
            if(!is_numeric($key))
            {
                $this->columns[] = $this->clean($key);
            }
            
            // Also Check to see if the value is numeric, if not, add quotes around the value
            if(!is_numeric($value))
            {
                $this->values[] = "'". $this->clean($value) ."'";
            }
            else
            {
                $this->values[] = $this->clean($value);
            }
        }
        
        // If we entered columns, then we use them, otherwise we do a plain insert
        if(count($this->columns) > 0)
        {
            $this->sql = "INSERT INTO ". $this->table ." (". implode(',', $this->columns) .") VALUES (". implode(',', $this->values) .")";
        }
        else
        {
            $this->sql = "INSERT INTO ". $this->table ." VALUES (". implode(',', $this->values) .")";
        }

        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: update()
| ---------------------------------------------------------------
|
| Update is used to initiate an UPDATE query
|
| @Param: $table - the table we are updating
| @Param: $data - an array of ( column => value )
|
*/	
    public function update($table, $data) 
    {
        // Empty out the old junk
        $this->clear();
        
        // Make sure our data is in an array format
        if(!is_array($data))
        {
            show_error(2, 'non_array', array('data', 'Querybuilder::update'));
            $data = array();
        }
        
        // Define our table and Init the SQL statement
        $this->table = $this->clean($table);
        $this->sql = "UPDATE ". $this->table ." SET ";

        // Start the loop of $keys = $values
        $count = count($data);
        $i = 1;
        
        // Add the column and values to 2 seperate arrays
        foreach($data as $key => $value)
        {
            $key = $this->clean($key);
            $value = $this->clean($value);
            
            // If the number is numeric, we do not add single quotes to the value
            if(is_numeric($value))
            {
                $this->sql .= "`".$key ."` = ". $value;
            }
            else
            {
                $this->sql .= "`".$key ."` = '". $value ."'";
            }
            
            // If we have more to go, add a ","
            if($i < $count) 
            {
                $this->sql .= ", ";
            }
            ++$i;
        }

        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: delete_from()
| ---------------------------------------------------------------
|
| Delete is used to delete from a table
|
| @Param: $table - the table we are deleting data from
|
*/
    public function delete_from($table) 
    {
        // Empty out the old junk
        $this->clear();
        
        // Define our table, build our query
        $this->table = $this->clean($table);
        $this->sql = "DELETE FROM ". $this->table;
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: from()
| ---------------------------------------------------------------
|
| Adds "FROM $table" to the query being built
|
| @Param: $table - the table name
|
*/
    public function from($table) 
    {
        $this->table = $this->clean($table);
        $this->sql .= " FROM ". $this->table;
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: where()
| ---------------------------------------------------------------
|
| Adds "WHERE $col ($opp) $val" to the query being built
|
| @Param: $col - the column, or an array of columns
| @Param: $val - value of the column, or an array of columns
| @Param: $opp - The operator, such as equals, greater then etc.
|   if $col and $val are arrays, this must be an array as well.
|
*/
    public function where($col, $val, $opp = '=') 
    {
        // Check for an array
        if(is_array($col))
        {
            // Add quotes on non-numeric values
            if(!is_numeric($val[0]))
            {
                $val[0] = "'". $val[0] ."'";
            }
            
            // Start the where clause
            $this->sql .= " WHERE ". $col[0] . $opp[0] . $val[0];
            
            // Unset the first col and value as we used those already.
            // Combine arrays, set our array pointer to 1
            unset($col[0], $val[0]);
            $array = array_combine($col, $val);
            $i = 1;
            
            // Loop through and add each if we have any
            if(count($array) > 0)
            {
                foreach($array as $key => $value)
                {
                    // Some light cleaning
                    $this->and_where($key, $value, $opp[$i]);
                    ++$i;
                }
            }
        }
        else
        {
            // Some light cleaning
            $col = $this->clean($col);
            $val = $this->clean($val);
            
            // Add quotes on non-numeric values
            if(!is_numeric($val))
            {
                $val = "'". $val ."'";
            }
            $this->sql .= " WHERE ". $col . $opp . $val;
        }
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: and_where()
| ---------------------------------------------------------------
|
| Adds "AND $col ($opp) $val" to the query being built
|
| @Param: $col - the column, or an array of columns
| @Param: $val - value of the column, or an array of columns
| @Param: $opp - The operator, such as equals, greater then etc.
|   if $col and $val are arrays, this must be an array as well.
|
*/
    public function and_where($col, $val, $opp = '=') 
    {
        // Check for an array
        if(is_array($col))
        {
            // Combine arrays, set our array pointer to 1
            $array = array_combine($col, $val);
            $i = 0;
            
            // Loop through and add each if we have any
            if(count($array) > 0)
            {
                foreach($array as $key => $value)
                {
                    // Some light cleaning
                    $this->and_where($key, $value, $opp[$i]);
                    ++$i;
                }
            }
        }
        else
        {
            // Some light cleaning
            $col = $this->clean($col);
            $val = $this->clean($val);
            
            // Add quotes on non-numeric values
            if(!is_numeric($val))
            {
                $val = "'". $val ."'";
            }
            $this->sql .= " AND ". $col . $opp . $val;
        }
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: or_where()
| ---------------------------------------------------------------
|
| Adds "OR $col ($opp) $val" to the query being built
|
| @Param: $col - the column
| @Param: $val - value of the column
|
*/
    public function or_where($col, $val, $opp = '=') 
    {
        // Check for an array
        if(is_array($col))
        {
            // Combine arrays, set our array pointer to 1
            $array = array_combine($col, $val);
            $i = 0;
            
            // Loop through and add each if we have any
            if(count($array) > 0)
            {
                foreach($array as $key => $value)
                {
                    // Some light cleaning
                    $this->or_where($key, $value, $opp[$i]);
                    ++$i;
                }
            }
        }
        else
        {
            // Some light cleaning
            $col = $this->clean($col);
            $val = $this->clean($val);
            
            // Add quotes on non-numeric values
            if(!is_numeric($val))
            {
                $val = "'". $val ."'";
            }
            $this->sql .= " OR ". $col . $opp . $val;
        }
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: join()
| ---------------------------------------------------------------
|
| A simple join method that adds " $type JOIN table1.col ON table2.col "
| to the query being built
|
| @Param: $type - the join type (inner, left, right, full)
| @Param: $col1 - the first "table.colname" in the query
| @Param: $col2 - the second "table2.colname2" in the query
|
*/
    public function join($type, $col1, $col2) 
    {
        // Make sure we have a valid type
        $type = strtoupper( " ".$type );
        switch($type)
        {
            case " INNER":
            case " LEFT":
            case " RIGHT":
            case " FULL":
                break;
            default:
                $type = " INNER";
                break;
        }
        
        // Build our statement
        $this->sql .= $type ." JOIN `". $col1 ."` ON `". $col2 ."`";
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: between()
| ---------------------------------------------------------------
|
| Adds a between statment into the current sql
|
| @Param: $val1 - the start of between
| @Param: $val2 - the end of between
|
*/
    public function between($val1, $val2) 
    {
        // Alittle cleaning
        $val1 = $this->clean($val1);
        $val2 = $this->clean($val2);
        
        // Build our statement
        $this->sql .= " BETWEEN `". $val1 ."` AND `". $val2 ."`";
        return $this;	
    }

/*
| ---------------------------------------------------------------
| Function: not_between()
| ---------------------------------------------------------------
|
| Adds a not between statment into the current sql
|
| @Param: $val1 - the start of between
| @Param: $val2 - the end of between
|
*/
    public function not_between($val1, $val2) 
    {
        // Alittle cleaning
        $val1 = $this->clean($val1);
        $val2 = $this->clean($val2);
        
        // Build our statement
        $this->sql .= " NOT BETWEEN `". $val1 ."` AND `". $val2 ."`";
        return $this;	
    }

/*
| ---------------------------------------------------------------
| Function: having()
| ---------------------------------------------------------------
|
| Adds "HAVING $having" to the query being built
|
| @Param: $having - what the table needs to have
|
*/
    public function having($having) 
    {
        $this->sql .= " HAVING ". $this->clean($having);
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: not_having()
| ---------------------------------------------------------------
|
| Adds "NOT HAVING $having" to the query being built
|
| @Param: $having - what the table needs to"not" have
|
*/
    public function not_having($having) 
    {
        $this->sql .= " NOT HAVING ". $this->clean($having);
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: group_by()
| ---------------------------------------------------------------
|
| Adds "GROUP BY $groupby" to the query being built
|
| @Param: $groupBy - What we are grouping by
|
*/
    public function group_by($groupBy) 
    {
        $this->sql .= " GROUP BY ". $this->clean($groupBy);
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: order_by()
| ---------------------------------------------------------------
|
| Adds "ORDER BY $orderBy" to the query being built
|
| @Param: $orderBy - How we are ording the result
| @Param: $type - How we order, for example: ASC, or DESC
|
*/
    public function order_by($orderBy, $type = 'ASC') 
    {
        $order = $this->clean($orderBy);
        $type = $this->clean($type);
        
        $this->sql .= " ORDER BY ". $order ." ". $type ;
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: limit()
| ---------------------------------------------------------------
|
| Adds "LIMIT $x OFFSET $y" to the query being built
|
| @Param: $x - the Limit
| @Param: $y - the result number to start on
|
*/
    public function limit($x, $y = 0)
    {
        // Alittle cleaning
        $x = $this->clean($x);
        $y = $this->clean($y);
        
        // Creat an offset if we have one, then build the sql statement
        $offset = ($y != 0) ? " OFFSET ". $y : "";
        $this->sql .= " LIMIT ". $x . $offset;
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: alias()
| ---------------------------------------------------------------
|
| Adds an alias (Ex: AS $as )
|
| @Param: $as - the alias
|
*/
    public function alias($as) 
    {
        // Build our statement
        $this->sql .= " AS ". $this->clean($as);
        return $this;	
    }

/*
| ---------------------------------------------------------------
| Function: add()
| ---------------------------------------------------------------
|
| Adds a custom string to the sql statment
|
| @Param: $string - The custom string to be added to the sql statement
|
*/
    public function add($string) 
    {
        // Build our statement
        $this->sql .= " ".$this->clean($string);
        return $this;	
    }

/*
| ---------------------------------------------------------------
| Function: clear()
| ---------------------------------------------------------------
|
| Clears out the query. Not really needed to be honest as a new
| query will automatically call this method.
|
*/
    public function clear()
    {
        $this->sql = '';
        $this->columns = array();
        $this->values = array();
    }

/*
| ---------------------------------------------------------------
| Function: clean()
| ---------------------------------------------------------------
|
| Clean the string using mysql_real_escape_string
|
*/
    public function clean($string)
    {
        // Mysql will convert int's to a string, we dont want that
        if(is_numeric($string))
        {
            return $string;
        }
        return mysql_real_escape_string($string);
    }
}
// EOF