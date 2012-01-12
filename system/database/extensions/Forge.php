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
    protected $cols = array();
    protected $keys = array();

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
| Function: add_field()
| ---------------------------------------------------------------
|
| The method is adds a column for the new table
|
| @Param: (String) $name - The name of the column
| @Param: (String) $type - The column type (Ex: INT, TEXT, VARCHAR)
| @Param: (Int) $size - The size constraint on the new field
| @Param: (Mixed) $default - The default value of the column
| @Param: (Bool) $auto - Auto Increment column?
| @Param: (Bool) $allow_null - Allow null value in col. ?
| @Param: (String) $comment - The Comment of the column
|
*/
    public function add_field($name, $type = NULL, $size = NULL, $default = NULL, $auto = FALSE, $allow_null = TRUE, $comment = NULL)
    {
        // Set the ID field always
        if($name == 'id')
        {
            // Set defaults if they were not specified
            if($size == NULL && $type == NULL) $auto = TRUE;
            if($type == NULL) $type = 'int';
            if($size == NULL) $size = 9;
            if($default == NULL) $default = 0;
            
            // Build our column array
            $col = array(
                'type' => $type,
                'size' => $size,
                'default' => $default,
                'auto_increment' => $auto,
                'allow_null' => $allow_null,
                'comment' => $comment
            );
        }
        else
        {
            // Set defaults if they were not specified
            if($type == NULL) $type = 'varchar';
            if($size == NULL) $size = 255;
            if($type != 'int' && $type != 'tinyint' && $type != 'smallint') $auto = FALSE;
            
            // Build our comlumn array
            $col = array(
                'type' => $type,
                'size' => $size,
                'default' => $default,
                'auto_increment' => $auto,
                'allow_null' => $allow_null,
                'comment' => $comment
            );
        }
        $this->cols[$name] = $col;
    }
    
/*
| ---------------------------------------------------------------
| Function: add_key()
| ---------------------------------------------------------------
|
| The method is adds a column key for the new table
|
| @Param: (String) $name - The name of the column
| @Param: (Bool) $primary - Is this a primary key?
|
*/
    public function add_key($name)
    {
        $this->keys[] = $name;
    }

 
/*
| ---------------------------------------------------------------
| Function: create_table()
| ---------------------------------------------------------------
|
| The method is used to create new tables in the database
|
| @Param: (String) $name - The name of the table
| @Param: (Bool) $if_not_exists - Only create table if it doesnt exist
|
*/
    public function create_table($name, $if_not_exists = TRUE, $charset = "utf8")
    {
        if( empty($this->cols) || empty($this->keys) )
        {
            show_error('You must first add columns and keys before creating a table', FALSE, E_ERROR);
            return FALSE;
        }
        
        // NOW, we can continue with creating the table
        $sql = 'CREATE TABLE ';

        // Add IF NOT EXISTS if set to true
        if($if_not_exists == TRUE)
        {
            $sql .= 'IF NOT EXISTS ';
        }
        
        // Add the table name
        $sql .= "`$name` (". PHP_EOL;
        
        // Loop through and add each column to the sql
        foreach($this->cols as $name => $col)
        {
            if($col['type'] != 'text')
            {
                $sql .= "\t`$name` ". strtolower($col['type']) ."(". $col['size'] .")";
            }
            else
            {
                $sql .= "\t`$name` text";
            }
            
            // Set allow Null
            if($col['allow_null'] == FALSE || $col['auto_increment'] == TRUE || $col['default'] != NULL)
            {
                $sql .= " NOT NULL";
            }
            
            // Set auto increment
            if($col['auto_increment'] == TRUE)
            {
                $sql .= " AUTO_INCREMENT";
            }
            
            // Add default
            else
            {
                ($col['default'] === NULL) ? $d = 'NULL' : $d = "'". $col['default'] ."'";
                $sql .= " DEFAULT ". $d;
            }
            
            // Add comment if one exists
            if($col['comment'] != NULL)
            {
                $sql .= " COMMENT '". $col['comment'] ."'";
            }
            
            // Add ending
            $sql .= ",". PHP_EOL;
        }
        
        // Add primary keys
        $sql .= "\tPRIMARY KEY (`". implode('`, `', $this->keys) ."`)". PHP_EOL;
        
        // Finish
        $sql .= ") DEFAULT CHARSET=". strtolower($charset) .";";
        $result = $this->DB->exec( $sql, FALSE );
        return ($result === FALSE) ? FALSE : TRUE;
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
| Function: add_column()
| ---------------------------------------------------------------
|
| The method is used to add columns to a  table
|
| @Param: (String) $name - The name of the table
| @Param: (String) $col_name - The name of the new column
| @Param: (String) $type - The column type (Ex: INT, TEXT, VARCHAR)
| @Param: (Int) $size - The size constraint on the new field
| @Param: (Mixed) $default - The default value of the column
| @Param: (Bool) $auto - Auto Increment column?
| @Param: (Bool) $allow_null - Allow null value in col. ?
| @Param: (String) $comment - The Comment of the column
|
*/
    public function add_column($name, $col_name, $type = 'varchar', $size = 255, $default = NULL, $auto = FALSE, $allow_null = TRUE, $comment = NULL)
    {
        // Prevent a syntax error here
        if($type != 'int' && $type != 'tinyint' && $type != 'smallint') $auto = FALSE;

        // Prefix
        $sql = "ALTER TABLE `$name` ADD ";

        // Loop through and add each column to the sql

        if($type != 'text')
        {
            $sql .= "\t`$col_name` ". strtolower($type) ."(". $size .")";
        }
        else
        {
            $sql .= "\t`$col_name` text";
        }
        
        // Set allow Null
        if($allow_null == FALSE || $auto == TRUE || $default != NULL)
        {
            $sql .= " NOT NULL";
        }
        
        // Set auto increment
        if($auto == TRUE)
        {
            $sql .= " AUTO_INCREMENT";
        }
        
        // Add default
        else
        {
            ($default === NULL) ? $d = 'NULL' : $d = "'". $default ."'";
            $sql .= " DEFAULT ". $d;
        }
        
        // Add comment if one exists
        if($comment != NULL)
        {
            $sql .= " COMMENT '". $comment ."'";
        }
        
        // Add ending
        $sql .= ";";

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
| @Param: (String) $name - The name of the table
| @Param: (String) $col_name - The name of the column
|
*/
    public function drop_column($name, $col_name)
    {
        $sql = "ALTER TABLE `$name` DROP COLUMN `$col_name`";
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
        $this->cols = array();
        $this->keys = array();
    }
}
// EOF