<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Library/Cache.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Cache
 */
namespace Library;

/**
 * Simple Cache class for the CMS
 *
 * @author      Steven Wilson 
 * @package     Library
 */
class Cache
{
    /**
     * The cache path
     * @var string
     */
    protected static $path;
    
    /**
     * Contructor method (called internally)
     *
     * Initiates the default cache path
     *
     * @return void
     */
    public static function Init()
    {
        self::$path = path( SYSTEM_PATH, "cache" );
    }
    
    /**
     * Sets the cache folder path
     *
     * @param string $path The path to the new cache folder
     *
     * @return void
     */
    public static function SetPath($path)
    {
        // Remove any trailing slashes
        $path = rtrim($path, '/\\');
        self::$path = str_replace( array('\\', '/'), DS, $path );
    }
    
    /**
     * Reads and returns the contents of a cache file
     *
     * @param string $name The name of the cache file
     *
     * @return mixed Returns the cache files contents
     */
    public static function Get($name)
    {
        // Define a file path
        $file = self::$path . DS . $name . '.cache';
        
        // check if our file exists
        if(file_exists($file))
        {
            // Get our file contents and Unserialize our data
            $data = file_get_contents($file);
            $data = unserialize($data);

            // Check out expire time, if expired, remove the file
            if($data['expire_time'] < time())
            {
                unlink($file);
                return false;
            }
            return $data['data'];
        }
        return false;
    }
    
    /**
     * Reads and returns the contents of a cache file
     *
     * @param string $name The name of the cache file
     * @param string $contents The contents to be stored in the cache
     * @param int $expire The expire time in seconds from now
     *
     * @return bool Returns true if the save is successfull, false otherwise
     */
    public static function Save($name, $contents, $expire = 86400)
    {
        // Define a file path
        $file = self::$path . DS . $name . '.cache';
        
        // Create our files contents
        $data = array(
            'expire_time' => (time() + $expire),
            'data' => $contents
        );

        // Save file and contents
        if(file_put_contents( $file, serialize($data) ))
        {
            // Try to put read/write permissions on the new file
            @chmod($file, 0777);
            return true;
        }
        return false;
    }
    
    /**
     * Deletes a cache file
     *
     * @param string $name The name of the cache file
     *
     * @return bool Returns true of the delete was successfull, false otherwise
     */
    public static function Delete($name)
    {
        // Define a file path
        $file = self::$path . DS . $name . '.cache';
        
        // Return the direct result of the deleting
        return unlink($file);
    }

/*
| ---------------------------------------------------------------
| Method: clear()
| ---------------------------------------------------------------
|
| Deletes all cache files
|
| @Return (Bool): true upon success
|
*/
    /**
     * Deletes all cache files from the cache folder
     *
     * @return bool Returns true of the delete was successfull, false otherwise
     */
    public static function Clear()
    {
        // get a list of all files and directories
        $files = scandir(self::$path);
        foreach($files as $file)
        {
            // Define a file path
            $file = self::$path . DS . $file;
        
            // We only want to delete the the cache files, not subfolders
            if($file[0] != "." && $file != 'index.html')
            {
                unlink($file); #Remove file
            }
        }
        return true;
    }
    
    /**
     * Reads and returns the expire time for the file in UNIX timestamp
     *
     * @param string $name The name of the cache file
     *
     * @return int Returns UNIX timestamp expire time
     */
    public static function ExpireTime($name)
    {
        // Define a file path
        $file = self::$path . DS . $name . '.cache';
        
        // check if our file exists
        if(file_exists($file))
        {
            // Get our file contents and Unserialize our data
            $data = file_get_contents($file);
            $data = unserialize($data);
            return $data['expire_time'];
        }
        return false;
    }
}

Cache::Init();

// EOF