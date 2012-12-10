<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Filesystem.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Filesystem
 */
namespace Core;

/**
 * A class built to easily manage files and directories
 *
 * @author      Steven Wilson 
 * @package     Core
 */
class Filesystem
{
    /**
     * This method is used to return whether a file OR directory is writable.
     *
     * @param string $path The complete path to the file or directory
     * @return bool
     */
    public static function IsWritable($path) 
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
    
    /**
     * This method is used to return whether a file OR directory is
     * readable and can be opened.
     *
     * @param string $path The complete path to the file or directory
     * @return bool
     */
    public static function IsReadable($path) 
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
    
    /**
     * Creates a new directory
     *
     * @param string $path  The complete path to the new directory
     * @param int $chmod The desired chmod on the folder
     * @return bool Returns true if the directory was created successfully.
     */
    public static function CreateDir($path, $chmod = 0777)
    {
        // Correct path
        $path = str_replace(array('/', '\\'), DS, $path);
        
        // Add trace for debugging
        // \Debug::trace("Creating directory '{$path}' (chmod: $chmod)", __FILE__, __LINE__);

        // Get current directory mask
        $oldumask = umask(0);
        if( !mkdir($path, $chmod, true) )
        {
            // Add trace for debugging
            // \Debug::trace("Failed to create directory '{$path}' (chmod: $chmod)", __FILE__, __LINE__);
            return false;
        }
        
        // Return to the old file mask, and return true
        umask($oldumask);
        return true;
    }
    
    /**
     * Removes a directory. You must use caution
     * with this method as its recursive, and will delete all sub files
     * and directories
     *
     * @param string $path  The complete path to the directory
     * @return bool Returns true if the directory was removed successfully.
     */
    public static function RemoveDir($path)
    {
        // Correct path
        $path = str_replace(array('/', '\\'), DS, $path);

        // Make sure we have a path, and not a file
        if(is_dir($path))
        {
            // Make sure our path is correct
            if($path[strlen($path)-1] != DS) $path = $path . DS;
            
            // Add trace for debugging
            // \Debug::trace("Removing directory '{$path}' recursivly.", __FILE__, __LINE__);
            
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
                    self::RemoveDir($file);
                }
                else 
                {
                    self::DeleteFile($file);
                }
            }
            
            // Close our path
            closedir($handle);
            
            // Clear stats cache and remove our current directory
            $result = rmdir($path);
            clearstatcache();
            return $result;
        }
        else
        {
            // Add trace for debugging
            // \Debug::trace("Unable to remove '{$path}' as it is not a directory!", __FILE__, __LINE__);
        }
        return false;
    }
    
    /**
     * Reads the contents a directory.
     *
     * @param string $path  The complete path to the directory
     * @param bool $detailed Detailed information about files and fodlers?
     * @param int $recursive Set the desired level or sub levels to read
     * as well, -1 is unlimited.
     * @return array[] | string[] <br />
     *      <ul>
     *           <li>'type' => "file" OR "folder"</li>
     *           <li>'name' => "Name of the file / folder"</li>
     *           <li>'path' => "/path/to/folder/" (with trailing slash)</li>
     *           <li>'file_list' => array() // Array of sub files and Dirs // Folders Only!</li>
     *      </ul>        
     *      IF $detailed is TRUE:
     *      <ul>
     *           <li>'size' => (int) filesize // Files only!</li>
     *           <li>'modified' => (int) Last modification timestamp</li>
     *           <li>'accessed' => (int) Last accessed timestamp</li>
     *      </ul>
     */
    public static function ReadDir($path, $detailed = false, $recursive = false)
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
                    if($recursive) $details['file_list'] = self::ReadDir($file, $detailed, $recursive - 1);
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
    
    /**
     * Creates a new file.
     *
     * @param string $file The complete path to the new file
     * @param string|mixed[] $contents The contents to place in the file.
     *   If contents are an array, they will be serialized using the php
     *   function <i>serialize()</i>. Default value is null
     * @return bool Returns true if the file was created successfully
     */
    public static function CreateFile($file, $contents = null)
    {
        // Correct path
        $file = str_replace(array('/', '\\'), DS, $file);
        
        // Add trace for debugging
        // \Debug::trace("Creating file '{$file}'", __FILE__, __LINE__);

        // Attempt to create the file
        $handle = @fopen($file, 'w+');
        if($handle)
        {
            // If contents are an array, then serialize them
            if(is_array($contents)) $contents = serialize($contents);
            
            // only add file contents if they are not null!
            if(!empty($contents))
            {
                fwrite($handle, $contents);
                fclose($handle);
            }
            
            // Return true if we are here
            return true;
        }
        
        // Add trace for debugging
        // \Debug::trace("Creation of file '{$file}' failed.", __FILE__, __LINE__);
        return false;
    }
    
    /**
     * Deletes a file
     *
     * @param string $file The complete path to the file
     * @return bool Returns true if the file was deleted successfully
     */
    public static function DeleteFile($file)
    {
        // Correct path
        $file = str_replace(array('/', '\\'), DS, $file);
        
        // Attempt to delete the file
        if( @unlink($file) )
        {
            // Add trace for debugging
            // \Debug::trace("Deleted file ". $file, __FILE__, __LINE__);
            return true;
        }
        
        // Add trace for debugging
        // \Debug::trace("Failed to delete file ". $file, __FILE__, __LINE__);
        return false;
    }
    
    /**
     * Lists an array of files in a directory
     *
     * @param string $path The complete path to the directory
     * @return string[] Returns an array of all the filenames in the directory
     */
    public static function ListFiles($path)
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
    
    /**
     * Lists an array of folders in a directory
     *
     * @param string $path The complete path to the directory
     * @param int $recursive Set the desired level or sub levels to read
     *    as well, -1 is unlimited.
     * @return array <br />
     *      <ul>
     *          <li>No Sub-Directories => (String) foldername</li>
     *          <li>With Sub-Directories => Array
     *              <ul>
     *                  <li>(String) Sub Directory Name</li>
     *              </ul>
     *          </li>
     *      </ul>
     */
    public static function ListFolders($path, $recursive = false)
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
                        $folders[] = array('name'=> $f, 'sub_dirs' => self::ListFolders($file, $recursive - 1));
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
    
    /**
     * Method is used to get the size of a file or folder
     *
     * @param string $path The complete path to the file / directory
     * @param bool $format Format the bytes into human readable? ( 11.7 MB )
     * @return int|string
     */
    public static function Size($path, $format = false)
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
                $total_size += self::Size($file, false);
            }
            
            // Close our path
            closedir($handle);
            return ($format == true) ? self::FormatSize($total_size) : $total_size;
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
            return ($format == true) ? self::FormatSize($total_size) : $total_size;
        }
    }
    
    /**
     * Copies the contents of a source file, to another file
     *
     * @param string $src The complete path to the source file
     * @param string $dest The complete path to the destination file
     * @return bool Returns true on success, or false
     */
    public static function Copy($src, $dest)
    {
        // Correct paths
        $src = str_replace(array('/', '\\'), DS, $src);
        $dest = str_replace(array('/', '\\'), DS, $dest);
        
        // Add trace for debugging
        // \Debug::trace("Copying the contents of '{$src}' to file '{$dest}'", __FILE__, __LINE__);
        
        // Make sure the src file exists
        if( !file_exists($src) )
        {
            // Add trace for debugging
            // \Debug::trace("Unable to copy the contents of '{$src}' because the file doesnt exist", __FILE__, __LINE__);
            return false;
        }
        
        // Copy the file
        if(!copy($src, $dest))
        {
            // Add trace for debugging
            // \Debug::trace("Error copying the contents of file '{$src}' to '{$dest}'", __FILE__, __LINE__);
            return false;
        }
        return true;
    }
    
    /**
     * Rename's a file
     *
     * @param string $src The complete path to the source file / folder
     * @param string $dest The complete path to the destination file / folder
     * @return bool Returns true on success, or false
     */
    public static function Rename($src, $dest)
    {
        // Correct paths
        $src = str_replace(array('/', '\\'), DS, $src);
        $dest = str_replace(array('/', '\\'), DS, $dest);
        
        // Add trace for debugging
        // \Debug::trace("Renaming file '{$src}' to '{$dest}'", __FILE__, __LINE__);
        
        // Make sure the src file exists
        if( !file_exists($src) )
        {
            // Add trace for debugging
            // \Debug::trace("Unable to rename file '{$src}' because it doesnt exist", __FILE__, __LINE__);
            return false;
        }
        
        // Rename the file
        return rename($src, $dest);
    }
    
    /**
     * Determines if the path given is a folder OR a file,
     * and removes it accordinly using this class's RemoveDir and
     * DeleteFile methods
     *
     * @param string $path The complete path to the source file / folder
     * @param string[] $files An array of files / folders to remove
     * @return bool Returns true on success, or false
     */
    public static function Delete($path, $files = array())
    {
        // Correct path
        $path = str_replace(array('/', '\\'), DS, $path);
        
        // Add trace for debugging
        // \Debug::trace("Deleting file/folder '{$path}'", __FILE__, __LINE__);
        
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
                    if( !self::Delete( $path . $f ) )
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
                return self::RemoveDir($path);
            }
        }
        else
        {
            return self::DeleteFile($path);
        }
    }
    
    /**
     * Converts an int or float size into a human readable format
     *
     * @param int|float $size The size to be converted
     * @return string The human readable size
     */
    protected static function FormatSize($size)
    {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
        return round($size, 2).$units[$i];
    }
}