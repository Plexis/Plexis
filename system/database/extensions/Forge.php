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
namespace System\Database\Extensions;

class Forge
{
    protected $DB;
    protected $lines = array();
    protected $keys = array();
    protected $table = '';
    protected $table_options = array();
    protected $mode = '';

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
| Creates the connection to the database using PDO
|
*/
    public function __construct($connection)
    {
        $this->DB = $connection;
    }

/*
| ---------------------------------------------------------------
| Function: add_column()
| ---------------------------------------------------------------
|
| The method is adds a column for the new table
|
| @Param: (String) $name - The name of the column
| @Param: (String) $type - The column type (Ex: int, text, varchar)
| @Param: (Int) $length - The size constraint on the new field
| @Param: (Array) $options - additional options for the table:
|   'allow_null' - (Bool) Allow the field to be null?
|   'default' - (String) The default value of the column if nothing exists
|   'comment' - (String) The comment of the column
|   'increments' - (Bool) & Int only - Does the value increment?
|   'primary_key' - (Bool) is this column a primary key?
|   'unique' - (Bool) Make the fields contents unique to the other rows?
|   'unsigned' - (Bool) & Int only - Column unsigned?
|
*/
    public function add_column($name, $type, $length = NULL, $options = array())
    {
        // Build our row sql
        if($this->mode == 'alter')
        {
            $sql = "ADD `$name` ";
        }
        else
        {
            $sql = "`$name` ";
        }
        
        // Process our column definition
        switch($type)
        {
            case "string":
            case "varchar":
                // Build the SQL
                $len = ( !is_null($length)) ?  $length  : 255;
                $sql .= "varchar(". $len .")";
                break;
                
            case "int":
            case "integer":
                // Build the SQL
                $len = ( !is_null($length)) ?  $length  : 11;
                $sql .= "int(". $len .")";
                $type = 'int';
                break;
                
            case "float":
                // Build the SQL
                $len = ( !is_null($length)) ?  $length  : 11;
                $sql .= "float(". $len .")";
                break;
                
            case "text":
                // Build the SQL
                $sql = "text";
                break;
                
            case "timestamp":
                $sql .= "timestamp";
                if( !isset($options['default'])) $options['default'] = "CURRENT_TIMESTAMP";
                break;
                
            default:
                return FALSE;
                break;
        }
        
        // Set binary / unsigned
        if($type == 'int' && isset($options['unsigned']) && $options['unsigned'] == TRUE)
        {
            $sql .= " unsigned";
        }
        
        // Set allow Null
        if(isset($options['allow_null']) && $options['allow_null'] == FALSE)
        {
            $sql .= " NOT NULL";
        }
        
        // Add default, use array_key_exists in case of NULL
        if(array_key_exists('default', $options))
        {
            if($options['default'] == NULL)
            {
                (isset($options['allow_null']) && $options['allow_null'] == FALSE) ? $d = "''" : $d = 'NULL';
            }
            else
            {
                $d = "'". $options['default'] ."'";
            }
            $sql .= " DEFAULT ". $d;
        }
        
        // Auto increment
        if($type == 'int' && isset($options['increments']) && $options['increments'] == TRUE)
        {
            $sql .= " AUTO_INCREMENT";
        }
        
        // Add unique
        if(isset($options['unique']) && $options['unique'] == TRUE)
        {
            $sql .= " UNIQUE";
        }
        
        // Add comment if one exists
        if(isset($options['comment']))
        {
            $sql .= " COMMENT '". $options['comment'] ."'";
        }
        
        // Add keys
        if(isset($options['primary']) && $options['primary'] == TRUE)
        {
            $this->keys[] = $name;
        }
        
        $this->lines[] = $sql;
        return $this;
    }

 
/*
| ---------------------------------------------------------------
| Function: create_table()
| ---------------------------------------------------------------
|
| The method is used to create new tables in the database. You
| need to add columns first but using the "add_columns" function
|
| @Param: (String) $name - The name of the table
| @Param: (Array) $options - an array of table options:
|   'engine' - Can specify DB engine (MyISAM, InnoDB)
|   'charset' - The table character set
|   'collate' - characterset collation
|   'row_format' - the row fomat
| @Param: (Bool) $if_not_exists - Only create table if it doesnt exist
|
*/
    public function create_table($name, $options = array(), $if_not_exists = TRUE)
    {
        // Set mode to create
        $this->mode = 'create';
        $this->table = $name;
        $this->table_options = array('if_not_exists' => $if_not_exists) + $options;
        return $this;
    }
    
/*
| ---------------------------------------------------------------
| Function: alter_table
| ---------------------------------------------------------------
|
| The method is used to add columns to a  table or alter it. If
| you add columns first (using the "add_columns" function), then
| running the function with no custom alter will add cols. to your
| selected table
|
| @Param: (String) $name - The name of the table
| @Param: (String) $custom - Custom alteration string
|
*/
    public function alter_table($name, $custom = FALSE)
    {
        // If passing custom alter data, add it
        if(is_string($custom))
        {
            $this->lines[] = $custom;
        }

        // Set mode to alter
        $this->mode = 'alter';
        $this->table = $name;
        return $this;
    }

/*
| ---------------------------------------------------------------
| Function: drop_table()
| ---------------------------------------------------------------
|
| The method is used to drop tables in the database
|
| @Param: (String) $name - The name of the table
|
*/
    public function drop_table($name)
    {
        $sql = "DROP TABLE IF EXISTS ". $name;
        $result = $this->DB->exec( $sql, FALSE );
        return ($result === FALSE) ? FALSE : TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: rename_table()
| ---------------------------------------------------------------
|
| The method is used to rename tables in the database
|
| @Param: (String) $name - The name of the table
| @Param: (String) $new_name - The new name of the table
|
*/
    public function rename_table($name, $new_name)
    {
        $sql = "ALTER TABLE ". $name ." RENAME TO ". $new_name;
        $result = $this->DB->exec( $sql, FALSE );
        return ($result === FALSE) ? FALSE : TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: drop_column()
| ---------------------------------------------------------------
|
| The method is used to drop columns out of a  table
|
| @Param: (String) $name - The name of the column
|
*/
    public function drop_column($name)
    {
        if($this->mode == 'alter')
        {
            $this->lines[] = "DROP COLUMN `$name`";
            return $this;
        }
        return FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Function: execute()
| ---------------------------------------------------------------
|
| This method is used to finalize and execute any added / droped
| coloumns made by the other functions, such as create_table().
|
*/
    public function execute()
    {
        if($this->mode == '' || empty($this->lines))
        {
            show_error('You cannot call this method without first selecting a mode and table, and adding columns or dropping them.', FALSE, E_ERROR);
            return FALSE;
        }
        
        switch($this->mode)
        {
            case "create":
                // Make sure we have a primary key
                if(empty($this->keys))
                {
                    show_error('You must first add a primary key before creating a table', FALSE, E_ERROR);
                    return FALSE;
                }
                
                // NOW, we can continue with creating the table
                $sql = 'CREATE TABLE ';

                // Add IF NOT EXISTS if set to true
                if($this->table_options['if_not_exists'] == TRUE) $sql .= 'IF NOT EXISTS ';
                
                // Add the table name
                $sql .= "`$this->table` (". PHP_EOL;
                
                // Loop through and add each column to the sql
                foreach($this->lines as $line)
                {
                    // Add this row to the sql
                    $sql .= "\t". $line .",". PHP_EOL;
                }
                
                // Add primary keys
                $sql .= "\tPRIMARY KEY (`". implode('`, `', $this->keys) ."`)". PHP_EOL . ")";
                
                // Finish
                if( !empty($this->table_options))
                {
                    // Check for a givin engine
                    if(isset($this->table_options['engine']))
                    {
                        $sql .= "ENGINE=". $this->table_options['engine'] ." ";
                    }
                    
                    // Check for charset
                    if(isset($this->table_options['charset']))
                    {
                        $sql .= "DEFAULT CHARSET=". $this->table_options['charset'] ." ";
                    }
                    
                    // Check forcollate
                    if(isset($this->table_options['collate']))
                    {
                        $sql .= "COLLATE=". $this->table_options['collate'] ." ";
                    }
                    
                    // Check for charset
                    if(isset($this->table_options['row_format']))
                    {
                        $sql .= "ROW_FORMAT=". $this->table_options['row_format'] ." ";
                    }
                }
                break;
                
            case "alter":
                // Start things off
                $sql = "ALTER TABLE `$this->table`". PHP_EOL;

                // Loop through and add each column to the sql
                $i = 1;
                $total = count($this->lines);
                foreach($this->lines as $line)
                {
                    // Add this row to the sql
                    $sql .= $line;
                    if($i != $total)  $sql .= ",". PHP_EOL;
                    ++$i;
                }
                break;
                
        }
        
        // Remove extra whitespace and close
        $sql = trim($sql);
        $sql .= ";";
        
        // Unset the keys and columns for the next one
        $this->reset();
        
        // Send the sql and return the result
        $result = $this->DB->exec( $sql, FALSE );
        return ($result === FALSE) ? FALSE : TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: reset()
| ---------------------------------------------------------------
|
| Resets the current query data
|
*/
    public function reset()
    {
        // Reset all vars
        $this->lines = array();
        $this->keys = array();
        $this->mode = '';
        $this->table = '';
        $this->table_options = '';
    }
}
// EOF