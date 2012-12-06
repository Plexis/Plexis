<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
*/
namespace Library;

class Cache
{
    protected static $path;

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
*/
    public static function Init()
    {
        self::$path = path( SYSTEM_PATH, "cache" );
    }

/*
| ---------------------------------------------------------------
| Method: SetPath()
| ---------------------------------------------------------------
|
| Sets the cache folder path
|
| @Param: (String) $path - The path to store cahce files.
| @Return (None)
|
*/
    public static function SetPath($path)
    {
        // Remove any trailing slashes
        $path = rtrim($path, '/\\');
        self::$path = str_replace( array('\\', '/'), DS, $path );
    }

/*
| ---------------------------------------------------------------
| Method: get()
| ---------------------------------------------------------------
|
| Reads and returns the contents of the cache file
|
| @Param: (String) $id - The id of the cache file
| @Return (Mixed): Returns the files contents, false otherwise
|
*/
    public static function Get($id)
    {
        // Define a file path
        $file = self::$path . DS . $id . '.cache';
        
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

/*
| ---------------------------------------------------------------
| Method: save()
| ---------------------------------------------------------------
|
| Saves the contents into the given file id.
|
| @Param: (String) $id - The id of the cache file
| @Param: (String) $contents - The contents to be stored in the cache
| @Param: (Int) $expire - The expire time in seconds from now.
| @Return (Bool): true upon success, false otherwise
|
*/
    public static function Save($id, $contents, $expire = 86400)
    {
        // Define a file path
        $file = self::$path . DS . $id . '.cache';
        
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

/*
| ---------------------------------------------------------------
| Method: delete()
| ---------------------------------------------------------------
|
| Deletes a cache file
|
| @Param: (String) $id - The id of the cache file
| @Return (Bool): true upon success, false otherwise
|
*/
    public static function Delete($id)
    {
        // Define a file path
        $file = self::$path . DS . $id . '.cache';
        
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
    
/*
| ---------------------------------------------------------------
| Method: expire_time()
| ---------------------------------------------------------------
|
| Reads and returns the expire time for the file in UNIX timestamp
|
| @Param: (String) $id - The id of the cache file
| @Return (Int): Returns UNIX timestamp expire time
|
*/
    public static function ExpireTime($id)
    {
        // Define a file path
        $file = self::$path . DS . $id . '.cache';
        
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