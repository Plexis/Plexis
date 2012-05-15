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

class Cache
{
    protected $path;

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
*/
    public function __construct()
    {
        $this->path = APP_PATH . DS . 'cache';
    }

/*
| ---------------------------------------------------------------
| Method: set_path()
| ---------------------------------------------------------------
|
| Sets the cache folder path
|
| @Param: (String) $path - The path to store cahce files.
| @Return (None)
|
*/
    public function set_path($path)
    {
        // Remove any trailing slashes
        $path = rtrim($path, '/');
        $this->path = str_replace( array('\\', '/'), DS, $path );
    }

/*
| ---------------------------------------------------------------
| Method: get()
| ---------------------------------------------------------------
|
| Reads and returns the contents of the cache file
|
| @Param: (String) $id - The id of the cache file
| @Return (Mixed): Returns the files contents, FALSE otherwise
|
*/
    public function get($id)
    {
        // Define a file path
        $file = $this->path . DS . $id . '.cache';
        
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
                return FALSE;
            }
            return $data['data'];
        }
        return FALSE;
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
| @Return (Bool): TRUE upon success, FALSE otherwise
|
*/
    public function save($id, $contents, $expire = 86400)
    {
        // Define a file path
        $file = $this->path . DS . $id . '.cache';
        
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
            return TRUE;
        }
        return FALSE;
    }

/*
| ---------------------------------------------------------------
| Method: delete()
| ---------------------------------------------------------------
|
| Deletes a cache file
|
| @Param: (String) $id - The id of the cache file
| @Return (Bool): TRUE upon success, FALSE otherwise
|
*/
    public function delete($id)
    {
        // Define a file path
        $file = $this->path . DS . $id . '.cache';
        
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
| @Return (Bool): TRUE upon success
|
*/
    public function clear()
    {
        // get a list of all files and directories
        $files = scandir($this->path);
        foreach($files as $file)
        {
            // Define a file path
            $file = $this->path . DS . $file;
        
            // We only want to delete the the cache files, not subfolders
            if($file[0] != "." && $file != 'index.html')
            {
                unlink($file); #Remove file
            }
        }
        return TRUE;
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
    public function expire_time($id)
    {
        // Define a file path
        $file = $this->path . DS . $id . '.cache';
        
        // check if our file exists
        if(file_exists($file))
        {
            // Get our file contents and Unserialize our data
            $data = file_get_contents($file);
            $data = unserialize($data);
            return $data['expire_time'];
        }
        return FALSE;
    }
}