<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author: 		Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License: 		GNU GPL v3
|
| ---------------------------------------------------------------
| Class: Filesystem()
| ---------------------------------------------------------------
|
| A class built to easily manage files and directories
|
*/
namespace System\Library;

class Filesystem
{
    protected $error = '';

/*
| ---------------------------------------------------------------
| Method: is_writable()
| ---------------------------------------------------------------
|
| This method is used to return whether a file OR directory is
| writable.
|
| @Param: $path - The complete path to the file or directory
| @Return: (Bool) TRUE or FALSE
|
*/
    public function is_writable($path) 
    {
        // Correct path
        $path = str_replace(array('/', '\\'), DS, $path);
        
        // Check if givin param is a path
        if(is_dir($path))
        {
            // Fix path, and Create a tmp file
            if($path[strlen($path)-1] != DS) $path = $path . DS;
            $file = $path . uniqid(mt_rand()) .'.tmp';
            
            // check tmp file for read/write capabilities
            $handle = @fopen($file, 'a');
            if ($handle === false) return false;
            
            // Close the folder and remove the temp file
            fclose($handle);
            unlink($file);
            return true;
        }
        else
        {
            // Make sure the file exists
            if( !file_exists($path) ) return false;
            
            // Attempt to open the file, and read contents
            $handle = @fopen($path, 'w');
            if($handle === false) return false;
            
            // Close the file, return true
            fclose($handle);
            return true;
        }
    }
  
/*
| ---------------------------------------------------------------
| Method: is_readable()
| ---------------------------------------------------------------
|
| This method is used to return whether a file OR directory is
| readable and can be opened.
|
| @Param: $path - The complete path to the file or directory
| @Return: (Bool) TRUE or FALSE
|
*/  
    public function is_readable($path) 
    {
        // Correct path
        $path = str_replace(array('/', '\\'), DS, $path);
        
        // Check if givin param is a path
        if(is_dir($path))
        {
            // Open the dir, and base read off of that
            $handle = @opendir($path);
            if($handle === false)
            {
                return false;
            }
            
            // Close the dir, and return true
            closedir($handle);
            return true;
        }
        else
        {
            // Make sure the file exists
            if( !file_exists($path) ) return false;
            
            // Attempt to open the file, and read contents
            $handle = @fopen($path, 'r');
            if($handle === false)
            {
                return false;
            }
            
            // Close the file, return true
            fclose($handle);
            return true;
        }
    }

/*
| ---------------------------------------------------------------
| Method: create_dir()
| ---------------------------------------------------------------
|
| This method is used to create a new directory.
|
| @Param: $path - The complete path to the new directory
| @Param: $chmod - The desired chmod on the folder
| @Return: (Bool) TRUE or FALSE
|
*/
    public function create_dir($path, $chmod = 0777)
    {
        // Correct path
        $path = str_replace(array('/', '\\'), DS, $path);

        // Get current directory mask
        $oldumask = umask(0);
        if( !mkdir($path, $chmod, true) )
        {
            return false;
        }
        
        // Return to the old file mask, and return true
        umask($oldumask);
        return true;
    }

/*
| ---------------------------------------------------------------
| Method: remove_dir()
| ---------------------------------------------------------------
|
| This method is used to remove a directory. You must use caution
| with this method as its recursive, and will delete all sub files
| and directories
|
| @Param: $path - The complete path to the directory
| @Return: (Bool) TRUE or FALSE
|
*/
    public function remove_dir($path)
    {
        // Correct path
        $path = str_replace(array('/', '\\'), DS, $path);

        // Make sure we have a path, and not a file
        if(is_dir($path))
        {
            // Make sure our path is correct
            if($path[strlen($path)-1] != DS) $path = $path . DS;
            
            // Open the directory
            $handle = @opendir($path);
            if ($handle === false) return false;
            
            // remove all sub directoires and files
            while(false !== ($f = readdir($handle)))
            {
                // Skip "." and ".." directories
                if($f == "." || $f == "..") continue;

                // make sure we establish the full path to the file again
                $file = $path . $f;
                
                // If is directory, call this method again to loop and delete ALL sub dirs.
                if(is_dir($file)) 
                {
                    $this->remove_dir($file);
                }
                else 
                {
                    $this->delete_file($file);
                }
            }
            
            // Close our path
            closedir($handle);
            
            // Clear stats cache and remove our current directory
            $result = rmdir($path);
            clearstatcache();
            return $result;
        }
        return false;
    }
    
/*
| ---------------------------------------------------------------
| Method: read_dir()
| ---------------------------------------------------------------
|
| This method is used to get an array of folders and files within.
|
| @Param: $path - The complete path to the directory
| @Param: $detailed - Detailed information about files and fodlers?
| @Param: $recursive - Set the desired level or sub levels to read
|   as well, -1 is unlimited.
| @Return: (Array):
|       array(
|           'type' => "file" OR "folder"
|           'name' => "Name of the file / folder"
|           'path' => "/path/to/folder/" (with trailing slash)
|           'file_list' => array() // Array of sub files and Dirs // Folders Only!
|              
|           IF $detailed is TRUE:
|               'size' => (int) filesize // Files only!
|               'modified' => (int) Last modification timestamp
|               'accessed' => (int) Last accessed timestamp
|       );
|
*/
    public function read_dir($path, $detailed = false, $recursive = false)
    {
        // Correct path
        $path = str_replace(array('/', '\\'), DS, $path);

        // Make sure we have a path, and not a file
        if(is_dir($path))
        {
            // Make sure our path is correct
            if($path[strlen($path)-1] != DS) $path = $path . DS;
            
            // Open the directory
            $handle = @opendir($path);
            if ($handle === false) return false;
            
            // array of files :)
            $files = array();
            
            // remove all sub directoires and files
            while(false !== ($f = readdir($handle)))
            {
                // Skip "." and ".." directories
                if($f == "." || $f == "..") continue;

                // If this is a bsic scan, treat it as so
                if(!$detailed && !$recursive)
                {
                    $files[] = $f;
                    continue;
                }

                // make sure we establish the full path to the file again
                $file = $path . $f;
                
                // Add our file detailed
                if(is_dir($file)) 
                {
                    $details = array(
                        'type' => 'folder',
                        'name' => $f,
                        'path' => $path,
                    );
                    
                    // Recursive?
                    if($recursive) $details['file_list'] = $this->read_dir($file, $detailed, $recursive - 1);
                }
                else 
                {
                    $details = array(
                        'type' => 'file',
                        'name' => $f,
                        'path' => $path,
                    );
                }
                
                // If detailed, get file/folder stats, and add them to the array as well
                if($detailed)
                {
                    $stat = @stat($file);
                    if(is_array($stat))
                    {
                        if($details['type'] == 'file') $details['size'] = $stat['size'];
                        $details['modified'] = $stat['mtime'];
                        $details['accessed'] = $stat['atime'];
                    }
                    else
                    {
                        // Error occured while getting file stats
                        if($details['type'] == 'file') $details['size'] = @filesize($path . $f);
                        $details['modified'] = filemtime($path . $f);
                        $details['accessed'] = fileatime($path . $f);
                    }
                }
                
                $files[] = $details;
            }
            
            // Close our path and remove the current dir
            closedir($handle);
            return $files;
        }
        return false;
    }

/*
| ---------------------------------------------------------------
| Method: create_file()
| ---------------------------------------------------------------
|
| This method is used to create a new file, and place contents
| within it (optional)
|
| @Param: $file - The complete path to the file
| @Param: $contents - The contents to place inside
| @Return: (Bool) TRUE or FALSE
|
*/
    public function create_file($file, $contents = null)
    {
        // Correct path
        $file = str_replace(array('/', '\\'), DS, $file);
        
        // Attempt to create the file
        if(touch($file))
        {
            // If contents are an array, then serialize them
            if(is_array($contents)) $contents = serialize($contents);
            
            // only add file contents if they are not null!
            if(!empty($contents))
            {
                return file_put_contents($file, $contents);
            }
            
            // Return true if we are here
            return true;
        }
        return false;
    }

/*
| ---------------------------------------------------------------
| Method: delete_file()
| ---------------------------------------------------------------
|
| This method is used to delete a file
|
| @Param: $file - The complete path to the file
| @Return: (Bool) TRUE or FALSE
|
*/
    public function delete_file($file)
    {
        // Correct path
        $file = str_replace(array('/', '\\'), DS, $file);
        
        // Attempt to delete the file
        if( @unlink($file) )
        {
            return true;
        }
        return false;
    }
    
/*
| ---------------------------------------------------------------
| Method: list_files()
| ---------------------------------------------------------------
|
| This method is used to list an array of file names in a directory
|
| @Param: $path - The complete path to the directory
| @Return: (Array)
|
*/
    public function list_files($path)
    {
        // Correct path
        $path = str_replace(array('/', '\\'), DS, $path);
        
        // Make sure we have a path, and not a file
        if(is_dir($path))
        {
            // Make sure our path is correct
            if($path[strlen($path)-1] != DS) $path = $path . DS;
            
            // Open the directory
            $handle = @opendir($path);
            if ($handle === false) return false;
            
            // Files array
            $files = array();
            
            // Loop through each file
            while(false !== ($f = readdir($handle)))
            {
                // Skip "." and ".." directories
                if($f == "." || $f == "..") continue;

                // make sure we establish the full path to the file again
                $file = $path . $f;
                
                // If is directory, call this method again to loop and delete ALL sub dirs.
                if( !is_dir($file) ) 
                {
                    $files[] = $f;
                }
            }
            
            // Close our path
            closedir($handle);
            return $files;
        }
        return false;
    }

/*
| ---------------------------------------------------------------
| Method: list_folders()
| ---------------------------------------------------------------
|
| This method is used to get an array of folders within a directory
|
| @Param: $path - The complete path to the directory
| @Param: $recursive - Set the desired level or sub levels to read
|   as well, -1 is unlimited.
| @Return: (Array):
|       array(
|           0 => "foldername"
|           1 => "foldername"
|           2 => array(
|               'name' => "foldername with sub dirs",
|               'sub_dirs' => array()
|       );
|
*/
    public function list_folders($path, $recursive = false)
    {
        // Correct path
        $path = str_replace(array('/', '\\'), DS, $path);
        
        // Make sure we have a path, and not a file
        if(is_dir($path))
        {
            // Make sure our path is correct
            if($path[strlen($path)-1] != DS) $path = $path . DS;
            
            // Open the directory
            $handle = @opendir($path);
            if ($handle === false) return false;
            
            // Folders array
            $folders = array();
            
            // Loop through each file
            while(false !== ($f = readdir($handle)))
            {
                // Skip "." and ".." directories
                if($f == "." || $f == "..") continue;

                // make sure we establish the full path to the file again
                $file = $path . $f;
                
                // If is directory, call this method again to loop and delete ALL sub dirs.
                if(is_dir($file)) 
                {
                    // Recursive?
                    if($recursive)
                    {
                        $folders[] = array('name'=> $f, 'sub_dirs' => $this->list_folders($file, $recursive - 1));
                    }
                    else
                    {
                        $folders[] = $f;
                    }
                }
            }
            
            // Close our path
            closedir($handle);
            return $folders;
        }
        return false;
    }

/*
| ---------------------------------------------------------------
| Method: size()
| ---------------------------------------------------------------
|
| This method is used to get the size of a file or folder
|
| @Param: $path - The complete path to the directory / file
| @Param: $format - Format the bytes into human readable? ( 11.7 MB )
| @Return: (String)
|
*/
    public function size($path, $format = false)
    {
        // Correct path
        $path = str_replace(array('/', '\\'), DS, $path);
        
        // Total size var
        $total_size = 0;
        
        // Make sure we have a path, and not a file
        if(is_dir($path))
        {
            // Make sure our path is correct
            if($path[strlen($path)-1] != DS) $path = $path . DS;
            
            // Open the directory
            $handle = @opendir($path);
            if ($handle === false) return false;
            
            // Folders array
            $folders = array();
            
            // Loop through each file
            while(false !== ($f = readdir($handle)))
            {
                // Skip "." and ".." directories
                if($f == "." || $f == "..") continue;

                // make sure we establish the full path to the file again
                $file = $path . $f;

                // Loop again for sub dirs...
                $total_size += $this->size($file, false);
            }
            
            // Close our path
            closedir($handle);
            return ($format == true) ? $this->format_size($total_size) : $total_size;
        }
        else
        {
            // Get most accurate filesize based on operating system
            $OS = strtoupper(PHP_OS);
            
            // Windows
            if(substr($OS, 0, 3) === 'WIN')
            {
                $total_size = exec("for %v in (\"".$path."\") do @echo %~zv");
            }
            
            // Try any other such as linux
            else
            {
                $total_size = exec("perl -e 'printf \"%d\n\",(stat(shift))[7];' ". $path);
            }
            
            // Just to make sure, try and return the filesize() if nothing else
            if($total_size == '0') $total_size = @filesize($path);
            return ($format == true) ? $this->format_size($total_size) : $total_size;
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: copy()
| ---------------------------------------------------------------
|
| A wrapper function for php's built in copy function. Includes
| checking if the file exists
|
| @Param: $src - The complete path to the source file
| @Param: $dest - The complete path to the destination file
| @Return: (Bool) TRUE or FALSE
|
*/
    public function copy($src, $dest)
    {
        // Correct paths
        $src = str_replace(array('/', '\\'), DS, $src);
        $dest = str_replace(array('/', '\\'), DS, $dest);
        
        // Make sure the src file exists
        if( !file_exists($src) ) return false;
        
        // Copy the file
        return copy($src, $dest);
    }

/*
| ---------------------------------------------------------------
| Method: rename()
| ---------------------------------------------------------------
|
| A wrapper function for php's built in rename function. Includes
| checking if the file exists
|
| @Param: $src - The complete path to the source file / folder
| @Param: $dest - The complete path to the destination file / folder
| @Return: (Bool) TRUE or FALSE
|
*/
    public function rename($src, $dest)
    {
        // Correct paths
        $src = str_replace(array('/', '\\'), DS, $src);
        $dest = str_replace(array('/', '\\'), DS, $dest);
        
        // Make sure the src file exists
        if( !file_exists($src) ) return false;
        
        // Rename the file
        return rename($src, $dest);
    }
    
/*
| ---------------------------------------------------------------
| Method: delete()
| ---------------------------------------------------------------
|
| This function determines if the path given is a folder OR a file,
|   and removes it accordinly using this class's remove_dir and
|   delete_file methods
|
| @Param: $path - The complete path to the source file / folder
| @Param: $files - An array of files / folders to remove
| @Return: (Bool) TRUE or FALSE
|
*/
    public function delete($path, $files = array())
    {
        // Correct path
        $path = str_replace(array('/', '\\'), DS, $path);
        
        if(is_dir($path))
        {
            // Make sure our path is correct
            if($path[strlen($path)-1] != DS) $path = $path . DS;

            // Check to see if we are just removing file or what :O
            if(!empty($files))
            {
                foreach($files as $f)
                {
                    // Attempt to delete the file, return false if even 1 fails
                    if( !$this->delete( $path . $f ) )
                    {
                        return false;
                    }
                }
                
                // End of loop, return true
                return true;
            }
            else
            {
                // Remove the whole dir
                return $this->remove_dir($path);
            }
        }
        else
        {
            return $this->delete_file($path);
        }
    }
    
    public function error_string()
    {
        return $this->error;
    }
    
    protected function format_size($size)
    {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
        return round($size, 2).$units[$i];
    }
}