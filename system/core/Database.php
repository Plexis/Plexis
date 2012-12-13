<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Database.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Database
 * @contains    DatabaseConnectError
 */
namespace Core;

// Register our class alias
use \Database\Driver;

/**
 * Database Factory Class
 *
 * @author      Steven Wilson 
 * @package     Database
 */
class Database
{
    /**
     * An array of all stored connections
     * @var \Database\Driver[]
     */
    protected static $connections = array();
    
    /**
     * Initiates a new database connection.
     *
     * @param string $name Name or ID of the connection
     * @param array $info The database connection information
     *     array(
     *       'driver'
     *       'host'
     *       'port'
     *       'database'
     *       'username'
     *       'password')
     * @param bool $new If connection already exists, setting to true
     *    will overwrite the old connection ID with the new connection
     * @return \Database\Driver Returns a Database Driver Object
     * @throws DatabaseConnectError if there is a database connection error
     */
    public static function Connect($name, $info, $new = false)
    {
        // If the connection already exists, and $new is false, return existing
        if(isset(self::$connections[$name]) && !$new)
            return self::$connections[$name];
        
        // Init a new connection
        try {
            self::$connections[$name] = new Driver($info);
        }
        catch( \Exception $e ) {
            throw new DatabaseConnectError($e->getMessage());
        }
        
        return self::$connections[$name];
    }
    
    /**
     * Returns the connection object for the given Name or ID
     *
     * @param string $name Name or ID of the connection
     * @return bool|\Database\Driver Returns a Database Driver Object,
     *    or false of the connection $name doesnt exist
     */
    public static function GetConnection($name)
    {
        if(isset(self::$connections[$name]))
            return self::$connections[$name];
        return false;
    }
}

/**
 * Database connection exception. Thrown when there is an error connecting to the database
 * @package Database
 * @subpackage Exceptions
 */
class DatabaseConnectError extends \Exception {}

// Register the autoloader, where to find the database driver class
AutoLoader::RegisterNamespace('Database', path(SYSTEM_PATH, "core", "database"));