<?php
/* 
| --------------------------------------------------------------
| 
| WowLib Framework for WoW Private Server CMS'
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Author:       Tony Hudgins
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/
namespace Wowlib;

class Characters implements iCharacters
{
    // Our DB Connection
    protected $DB;
    protected $parent;
    protected $config;
    
    // Array of options
    protected $queryConfig = array(
        'faction' => 0,
        'limit' => 50,
        'offset' => 0,
        'where' => '',
        'bind' => array(),
        'orderby' => '',
        'direction' => 'ASC'
    );
    
    // An array of columnID => columnName
    protected $cols = array();
    

    
/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($parent)
    {
        // If the characters database is offline, throw an exception!
        if(!is_object( $parent->getCDB() )) throw new \Exception('Character database offline');
        
        // Set our database conntection
        $this->parent = $parent;
        $this->DB = $parent->getCDB();
        $this->config = $parent->getConfig();
        
        // Get our column names and ID's
        $this->cols = $this->config['characterColumns'];
        
        // Require the character class
        require path(dirname(__FILE__), 'Character.php');
    }
    
/*
| ---------------------------------------------------------------
| Method: nameExists
| ---------------------------------------------------------------
|
| This method is used to determine if a character name is available
|
| @Param: (String) $name - The character name we are looking up
| @Retrun:(Bool): TRUE if the name is available, FALSE otherwise
|
*/     
    public function nameExists($name)
    {
        // Build our column names
        $guid = $this->config['characterColumns']['guid'];
        $cname = $this->config['characterColumns']['name'];
        $table = $this->config['characterTable'];
        
        // Build our query
        $query = "SELECT `{$guid}` FROM `{$table}` WHERE `{$cname}`=?";
        $exists = $this->DB->query( $query, array($name) )->fetchColumn();
        return ($exists !== false) ? true : false;
    }
    
/*
| ---------------------------------------------------------------
| Method: fetch
| ---------------------------------------------------------------
|
| This method is used to return an array of character information
|
| @Param: (Int) $id - The character ID
| @Retrun: (Object) Returns a Character Object class
|
*/  
    public function fetch($id)
    {
        // Array of columns that our query needs
        $table = $this->config['characterTable'];
        $columns = array();
        
        // Prepare the column name for the WHERE statement based off of $id type
        $col = (is_int($id)) ? $cols['guid'] : $cols['name'];
        
        // Filter out false columns
        foreach($this->cols as $c)
        {
            if($c != false) $columns[] = $c;
        }
        
        // Create our columns string
        $cols = "`". implode('`, `', $columns) ."`";
    
        // Load the character
        $query = "SELECT {$cols} FROM `{$table}` WHERE `{$col}`= ?;";
        $data = $this->DB->query($query, array($id))->fetchRow();
        if(!is_array($data)) return false;
        
        // Get the parent namespace if a sub class exists
        if($this->parent->classExists('Character'))
        {
            $namespace = $this->parent->getDriverNamespace();
            $class = $namespace ."\\Character";
        }
        else
        {
            $class = 'Wowlib\Character';
        }
        
        // Build our query
        try {
            $character = new $class($data, $this, $this->cols);
        }
        catch (\Exception $e) {
            $character = false;
        }
        
        return $character;
    }

/*
| ---------------------------------------------------------------
| Method: getOnlineCount
| ---------------------------------------------------------------
|
| Returns the amount of characters currently online
|
| @Param: (Int) $faction - Faction ID, 1 = Ally, 2 = Horde, 0 = Both
| @Retrun: (Int): Returns the amount on success, FALSE otherwise
|
*/     
    public function getOnlineCount($faction = 0)
    {
        //Table name, and columns that our query needs
        $table = $this->config['characterTable'];
        $online = $this->config['characterColumns']['online'];
        $race = $this->config['characterColumns']['race'];
        $where = '';
        
        // Add where for faction if this is a faction specific request
        if($faction != 0)
        {
            $faction = ($faction == 1) ? 'alliance' : 'horde';
            $races = $this->config['race'][$faction];
            foreach($races as $key => $v)
            {
                $where .= "`{$race}` = {$key} OR ";
            }
            $where = " AND (" . substr($where, 0, -3) .")";
        } 
        
        // Return the query result
        $query = "SELECT COUNT(`{$online}`) FROM `{$table}` WHERE `{$online}`=1". $where;
        return $this->DB->query( $query )->fetchColumn();
    }
    
/*
| ---------------------------------------------------------------
| Method: getOnlineList
| ---------------------------------------------------------------
|
| This method returns a list of characters online
|
| @Param: (Array) An array of query configurations (See Documentation)
| @Retrun: (Array): An array of character objects
|
*/     
    public function getOnlineList($config = array())
    {
        // Merge Configs
        $config = array_merge($this->queryConfig, $config);
        
        // Array of columns that our query needs
        $online = $this->config['characterColumns']['online'];
        $race = $this->config['characterColumns']['race'];
        
        // pre build the query...
        $where = "`{$online}`= 1";
        
        // Add where for faction if this is a faction specific request
        if($config['faction'] != 0)
        {
            $twhere = '';
            $faction = ($config['faction'] == 1) ? 'alliance' : 'horde';
            $races = $this->config['race'][$faction];
            foreach($races as $key => $v)
            {
                $twhere .= "`{$race}` = {$key} OR ";
            }
            $where .= " AND (" . substr($twhere, 0, -3) .")";
        }
        
        // Append custom Where statement
        if(!empty($config['where'])) $config['where'] .= " AND ";
        $config['where'] .= $where; 
        
        // Prepare the statement
        $statement = $this->prepare($config);
        
        // Execute the statement
        $statement->execute();
        $online = array();
        
        // Get the parent namespace if a sub class exists
        if($this->parent->classExists('Character'))
        {
            $namespace = $this->parent->getDriverNamespace();
            $class = $namespace ."\\Character";
        }
        else
        {
            $class = 'Wowlib\Character';
        }
        
        // Build an array of character objects
        while($row = $statement->fetch())
        {
            $online[] = new $class($row, $this, $this->cols);
        }
        
        return $online;
    }
    
/*
| ---------------------------------------------------------------
| Method: getCharacterList
| ---------------------------------------------------------------
|
| This method is used to list all the characters from the characters
| database.
|
| @Param: (Array) An array of query configurations (See Documentation)
| @Retrun: (Array): An array of characters
|
*/
    public function getCharacterList($config = array())
    {
        // Merge Configs
        $config = array_merge($this->queryConfig, $config);
        
        // Array of columns that our query needs
        $race = $this->config['characterColumns']['race'];
        $where = '';
        
        // Add where for faction if this is a faction specific request
        if($config['faction'] != 0)
        {
            $faction = ($faction == 1) ? 'alliance' : 'horde';
            $races = $this->config['race'][$faction];
            foreach($races as $key => $v)
            {
                $where .= "`{$race}` = {$key} OR ";
            }
            $where = "(" . substr($where, 0, -3) .")";
        }
        
        // Append custom Where statement
        if(!empty($config['where'])) $config['where'] .= " AND ";
        $config['where'] .= $where; 
        
        // Prepare the statement
        $statement = $this->prepare($config);
        
        // Execute the statement
        $statement->execute();
        $online = array();
        
        // Get the parent namespace if a sub class exists
        if($this->parent->classExists('Character'))
        {
            $namespace = $this->parent->getDriverNamespace();
            $class = $namespace ."\\Character";
        }
        else
        {
            $class = 'Wowlib\Character';
        }
        
        // Build an array of character objects
        while($row = $statement->fetch())
        {
            $online[] = new $class($row, $this, $this->cols);
        }
        
        return $online;
    }

/*
| ---------------------------------------------------------------
| Method: topKills
| ---------------------------------------------------------------
|
| This method returns a list of the top chacters with kills
|
| @Param: (Array) An array of query configurations (See Documentation)
| @Retrun: (Array): An array of characters ORDERED by kills
|
*/      
    public function topKills($config = array())
    {
        // Merge Configs
        $config = array_merge($this->queryConfig, $config);
        
        // Array of columns that our query needs
        $race = $this->config['characterColumns']['race'];
        $kills = $this->config['characterColumns']['totalKills'];
        
        // Add where for faction if this is a faction specific request
        if($config['faction'] != 0)
        {
            $faction = ($faction == 1) ? 'alliance' : 'horde';
            $races = $this->config['race'][$faction];
            foreach($races as $key => $v)
            {
                $twhere .= "`{$race}` = {$key} OR ";
            }
            if(!empty($config['where'])) $config['where'] .= ' AND ';
            $config['where'] .= "(" . substr($twhere, 0, -3) .")";
        }
        
        // Append the ORDER BY if the user didnt set this in the config
        if(empty($config['orderby'])) $config['orderby'] = $kills;
        if(empty($config['direction'])) $config['direction'] = 'DESC';
        
        // Prepare the statement
        $statement = $this->prepare($config);
        
        // Execute the statement
        $statement->execute();
        $online = array();
        
        // Get the parent namespace if a sub class exists
        if($this->parent->classExists('Character'))
        {
            $namespace = $this->parent->getDriverNamespace();
            $class = $namespace ."\\Character";
        }
        else
        {
            $class = 'Wowlib\Character';
        }
        
        // Build an array of character objects
        while($row = $statement->fetch())
        {
            $online[] = new $class($row, $this, $this->cols);
        }
        
        return $online;
    }
 
/*
| ---------------------------------------------------------------
| Method: delete
| ---------------------------------------------------------------
|
| This method removes the character from the characters DB
|
| @Param: (Int) $id - The character id we are deleteing
| @Retrun: (Bool): True on success, FALSE otherwise
|
*/ 
    public function delete($id)
    {
        // A list of (table => character_id_col_name) to remove character info from
        // The more tables listed, the more we can delete this character
        $tables = $this->config['deleteCharacterTables'];
        
        foreach($tables as $table => $col)
        {
            $result = $this->DB->delete($table, "`{$col}`= {$id}");
            if($result === false) return false;
        }
        
        return true;
    }
    
/*
| ---------------------------------------------------------------
| Method: listCharactersDatatables
| ---------------------------------------------------------------
|
| This method returns a list of characters, formatted for datatables
| ajax.
|
| @Param: (Int) $acct - The account ID. 0 = all characters from all
|   accounts
| @Param: (Bool) $online - Only list online players?
| @Retrun: (Array): An array of characters
|
*/     
    public function listCharactersDatatables($acct = 0, $online = false)
    {
        // Load the ajax model
        $ajax = load_class('Loader')->model("Ajax_Model", "ajax");
  
        /* 
        * Dwsc: Array of database columns which should be read and sent back to DataTables. 
        * Format: id, name, character level, race ID, class ID, Gender ID, Zone ID, Account ID, And status
        */
        $cols = array( 
            $this->cols['guid'], 
            $this->cols['name'], 
            $this->cols['level'], 
            $this->cols['race'], 
            $this->cols['class'], 
            $this->cols['gender'], 
            $this->cols['zoneId'], 
            $this->cols['account'], 
            $this->cols['online'] 
        );
        
        /* Character ID column name */
        $index = $this->cols['guid'];
        
        /* characters table name to use */
        $table = $this->config['characterTable'];
        
        /* where statment */
        $where = ($online == true) ? "`{$this->cols['online']}` = 1" : '';
        
        /* And Where statment */
        if($acct != 0) $where .= ($online == true) ? " AND `{$this->cols['account']}` = {$acct}" : "`{$this->cols['account']}` = {$acct}";
        
        /* Process the request */
        return $ajax->process_datatables($cols, $index, $table, $where, $this->DB);
    }


/*
| -------------------------------------------------------------------------------------------------
|                               AT LOGIN FLAGS
| -------------------------------------------------------------------------------------------------
*/


/*
| ---------------------------------------------------------------
| Method: loginFlags()
| ---------------------------------------------------------------
|
| This method is used to return a list of "at login" flags this
| core / revision is able to do. Please note, the functions must
| exist!
|
| @Retrun: (Array): An array of true / false flags
|
*/ 
    public function loginFlags()
    {
        return $this->config['loginFlags'];
    }
    
/*
| ---------------------------------------------------------------
| Method: flagToBit()
| ---------------------------------------------------------------
|
| This method is used to return the bitmask flag for the givin flag 
| name
|
| @Param: (String) $flag - The flag name we are getting the bit for
| @Retrun: (Int | Bool): The bitmask on success, False otherwise
|
*/
    public function flagToBit($flag)
    {
        // only list available flags
        $flags = $this->config['loginFlags'];
        return (isset($flags[ $flag ])) ? $flags[ $flag ] : false;
    }
    
    
/*
| -------------------------------------------------------------------------------------------------
|                               HELPER FUNCTIONS
| -------------------------------------------------------------------------------------------------
*/


/*
| ---------------------------------------------------------------
| Method: raceToText()
| ---------------------------------------------------------------
*/
    public function raceToText($id)
    {
        // Get all races from the config file
        $horde = $this->config['race']['horde'];
        $ally = $this->config['race']['alliance'];
        $races = $ally + $horde;
        
        // Check if the race is set, if not then Unknown
        if(isset($races[$id]))
        {
            return $races[$id];
        }
        return "Unknown";
    }

/*
| ---------------------------------------------------------------
| Method: classToText()
| ---------------------------------------------------------------
*/
    public function classToText($id)
    {
        // Check if the class is set, if not then Unknown
        if(isset($this->config['class'][$id]))
        {
            return $this->config['class'][$id];
        }
        return "Unknown";
    }

/*
| ---------------------------------------------------------------
| Method: genderToText()
| ---------------------------------------------------------------
*/
    public function genderToText($id)
    {
        // Check if the gender is set, if not then Unknown
        if(isset($this->config['gender'][$id]))
        {
            return $this->config['gender'][$id];
        }
        return "Unknown";
    }
    
/*
| ---------------------------------------------------------------
| Method: getDB()
| ---------------------------------------------------------------
|
| This method returns the database connection object to the 
| characters database
|
| @Return: (Object)
|
*/
    public function getDB()
    {
        return $this->DB;
    }
    
/*
| ---------------------------------------------------------------
| Method: getColumnById()
| ---------------------------------------------------------------
|
| This method returns the column name, from the characters table
|
| @Param: (String) $col - The column ID ID
| @Return: (String)
|
*/
    public function getColumnById($col)
    {
        // Make sure the config key exists
        if(!isset($this->config["characterColumns"][$col])) return false;
        
        return $this->config["characterColumns"][$col];
    }
    
    
/*
| -------------------------------------------------------------------------------------------------
|                               Characters Table Query Builder
| -------------------------------------------------------------------------------------------------
*/
    protected function _buildQuery($config)
    {
        // Array of columns that our query needs
        $table = $this->config['characterTable'];
        
        // Filter out false column names
        $columns = array();
        foreach($this->cols as $c)
        {
            if($c !== false) $columns[] = $c;
        }
        
        // Prepare the column names
        $cols = "`". implode('`, `', $columns) ."`";
    
        // pre build the query...
        $query = "SELECT {$cols} FROM `{$table}`";
        
        // Append Where statement
        if($config['where'] != null) $query .= " WHERE {$config['where']}";
        
        // Append OrderBy statement
        $dir = (strtoupper($config['direction']) == 'ASC') ? 'ASC' : 'DESC';
        if($config['orderby'] != null) $query .= " ORDER BY `{$config['orderby']}` {$dir}";
        
        // Append Limits
        $query .= " LIMIT {$config['offset']}, {$config['limit']}";
        
        // Return the query
        return $query;
    }
    
    protected function prepare($config)
    {
        // Fetch our pre built query
        $query = $this->_buildQuery($config);
        
        // Prepare the statement, and bind params
        $stmt = $this->DB->prepare($query);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        if(is_array($config['bind']) && !empty($config['bind']))
        {
            foreach($config['bind'] as $key => $var)
            {
                if(is_int($var))
                    $stmt->bindParam($key, $var, \PDO::PARAM_INT);
                else
                    $stmt->bindParam($key, $var, \PDO::PARAM_STR, strlen($var));
            }
        }
        
        // Return the statement
        return $stmt;
    }
}
// EOF