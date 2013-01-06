<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/IO/DirectoryInfo.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    DirectoryInfo
 */
namespace Core\IO;

/**
 * A Directory class used to preform advanced operations and provide information
 * about the directory.
 *
 * @author      Steven Wilson 
 * @package     Core
 * @subpackage  IO
 */
class DirectoryInfo
{
    /**
     * An array of files in this directory
     * @var string[]
     */
    protected $filelist = array();
    
    /**
     * An array of sub directories in this directory
     * @var string[]
     */
    protected $subdirs = array();
    
    /**
     * Full path to the parent directory
     * @var string
     */
    protected $parentDir;
    
    /**
     * Full path to the current directory
     * @var string
     */
    protected $rootPath;
    
    /**
     * Class Constructor
     *
     * @param string $path The full path the the directory
     * @param bool $create Create the directory if it doesnt exist?
     *
     * @throws \IOException Thrown if the $path directory doesnt exist,
     *   $create is set to true, and there was an error creating the directory
     * @throws \DirectoryNotFoundException If the $path directory does not exist,
     *   and $create is set to false.
     * @throws \SecurityException Thrown if the $path directory could not be opened
     *   for various security reasons such as permissions.
     *
     * @return void
     */
    public function __construct($path, $create = false)
    {
        // If the directory doesnt exist
        if(!is_dir($path))
        {
            // Are we trying to create a new dir?
            if($create)
            {
                if(!$this->createDir($path, 0777))
                {
                    throw new \IOException("Unable to create directory '{$path}'.");
                }
            }
            else
            {
                // Cant continue from here D:
                throw new \DirectoryNotFoundException("Directory '{$path}' does not exist.");
            }
        }
        
        // Set path
        $this->rootPath = rtrim($path, DS) . DS;
        $this->parentDir = dirname($path) . DS;
        
        // Scan the fodler to get a list of files and subdirs
        $this->_scanDir();
    }
    
    /**
     * Returns the base folder name
     *
     * @return string
     */
    public function name()
    {
        return basename($this->rootPath);
    }
    
    /**
     * Returns the full path to the folder, including the folder name
     *
     * @return string
     */
    public function fullpath()
    {
        return $this->rootPath;
    }
    
    /**
     * Returns the amount of files in the directory
     *
     * @return int
     */
    public function fileCount() 
    {
        return sizeof($this->filelist);
    }
    
    /**
     * Returns the amount of subdirectories in the directory
     *
     * @return int
     */
    public function dirCount() 
    {
        return sizeof($this->subdirs);
    }
    
    /**
     * Returns if a file exists within the directory
     *
     * @param string $name The name of the file
     *
     * @return bool
     */
    public function hasFile($name) 
    {
        return in_array($name, $this->filelist);
    }
    
    /**
     * Returns if a subdirectory exists within the directory
     *
     * @param string $name The name of the subdirectory
     *
     * @return bool
     */
    public function hasDir($name) 
    {
        return in_array($name, $this->subdirs);
    }
    
    /**
     * Fetches a file within the directory
     *
     * @param string $name The name of the file to fetch
     *
     * @return FileInfo|bool The fileinfo object of $name file, or
     *   false if the file does not exist.
     */
    public function getFile($name) 
    {
        $return = false;
        try {
            $return = new FileInfo($this->rootPath . $name);
        }
        catch( \FileNotFoundException $e ) {}
        
        return $return;
    }
    
    /**
     * Fetches an array of files withing the directory
     *
     * @param bool $sortAsc Sort by asscending? If set to false, 
     *   files will be set descending.
     *
     * @return FileInfo[] The fileinfo object of each file in the directory
     */
    public function getFileList($sortAsc = true) 
    {
        $return = array();
        foreach($this->filelist as $file)
        {
            $return[] = new FileInfo($this->rootPath . $file);
        }
        
        return ($sortAsc) ? $return : array_reverse($return);
    }
    
    /**
     * Fetches a subdirectory within the directory
     *
     * @param string $name The name of the directory to fetch
     *
     * @throws \DirectoryNotFoundException If the $path directory does not exist,
     *   and $create is set to false.
     * @throws \SecurityException Thrown if the $path directory could not be opened
     *   for various security reasons such as permissions.
     *
     * @return DirectoryInfo The fileinfo object of $name file.
     */
    public function getDir($name) 
    {
        return new DirectoryInfo($this->rootPath . $name);
    }
    
    /**
     * Fetches an array of subdirectory's within the directory
     *
     * @param bool $sortAsc Sort by asscending? If set to false, 
     *   files will be set descending.
     *
     * @return DirectoryInfo[]|string[] Returns an array of DirectoryInfo for each 
     *   sub directory. If there was an exception thrown while opening a sub directory, a
     *   string filepath will be in place of the DirectoryInfo class.
     */
    public function getDirList($sortAsc = true) 
    {
        $return = array();
        foreach($this->subdirs as $dir)
        {
            try {
                $return[] = new DirectoryInfo($this->rootPath . $dir);
            }
            catch( \Exception $e ) {
                $return[] = $this->rootPath . $dir;
            }
        }
        
        return ($sortAsc) ? $return : array_reverse($return);
    }
    
    /**
     * Fetches an array of files with a specified file extension
     *
     * @param string $ext The file extension, without the dot.
     *
     * @return FileInfo[] The fileinfo object of each file in the directory
     *   with the specified extension
     */
    public function getFilesWithExt($ext) 
    {
        // Make sure we dont have a dot
        $ext = ltrim($ext, '.');
        $return = array();
        
        foreach($this->filelist as $file)
        {
            $e = pathinfo($this->rootPath . $file, PATHINFO_EXTENSION);
            if($e == $ext)
                $return[] = new FileInfo($this->rootPath . $file);
        }
        
        return $return;
    }
    
    /**
     * Fetches an array of files that have been modified since the timestamp provided
     *
     * @param int $timestamp The timestamp
     *
     * @return FileInfo[] The fileinfo object of each file in the directory
     *   that have been modified since the provided timestamp
     */
    public function getFilesModifiedSince($timestamp) 
    {
        $return = array();
        
        foreach($this->filelist as $file)
        {
            try {
                $_file = new FileInfo($this->rootPath . $file);
                if($_file->modified() > $timestamp)
                    $return[] = $_file;
            }
            catch( \Exception $e ) {}
        }
        
        return $return;
    }
    
    /**
     * Fetches an array of files that have been modified before the timestamp provided
     *
     * @param int $timestamp The timestamp
     *
     * @return FileInfo[] The fileinfo object of each file in the directory
     *   that have been modified before the provided timestamp
     */
    public function getFilesModifiedBefore($timestamp) 
    {
        $return = array();
        
        foreach($this->filelist as $file)
        {
            try {
                $_file = new FileInfo($this->rootPath . $file);
                if($_file->modified() < $timestamp)
                    $return[] = $_file;
            }
            catch( \Exception $e ) {}
        }
        
        return $return;
    }
    
    /**
     * Fetches an array of files that have been accessed since the timestamp provided
     *
     * @param int $timestamp The timestamp
     *
     * @return FileInfo[] The fileinfo object of each file in the directory
     *   that have been accessed since the provided timestamp
     */
    public function getFilesAccessedSince($timestamp) 
    {
        $return = array();
        
        foreach($this->filelist as $file)
        {
            try {
                $_file = new FileInfo($this->rootPath . $file);
                if($_file->accessed() > $timestamp)
                    $return[] = $_file;
            }
            catch( \Exception $e ) {}
        }
        
        return $return;
    }
    
    /**
     * Fetches an array of files that have been accessed before the timestamp provided
     *
     * @param int $timestamp The timestamp
     *
     * @return FileInfo[] The fileinfo object of each file in the directory
     *   that have been accessed before the provided timestamp
     */
    public function getFilesAccessedBefore($timestamp) 
    {
        $return = array();
        
        foreach($this->filelist as $file)
        {
            try {
                $_file = new FileInfo($this->rootPath . $file);
                if($_file->accessed() < $timestamp)
                    $return[] = $_file;
            }
            catch( \Exception $e ) {}
        }
        
        return $return;
    }
    
    
    /**
     * Fetches or sets the permissions of the directory
     *
     * @param int $ch The permission level to set on the directory (chmod).
     *   If left unset, the current chmod will be returned.
     *
     * @return int|bool Returns the current folder chmod if $ch is left null,
     *   otherwise, returns the success value of setting the permissions.
     */
    public function chmod($ch = null) 
    {
        if(empty($ch))
            return fileperms($this->rootPath);
            
        return chmod($this->rootPath, $ch);
    }
    
    /**
     * Moves the directory and all of its contents to a new path.
     *
     * The old directory will not be removed until the new directory is created successfully.
     *
     * @param string $newPath The full path to move the contents of this
     *   folder to.
     *
     * @throws \IOException Thrown if there was an error creating the new directory path
     * @throws \SecurityException Thrown if the $newPath directory could not be opened
     *   for various security reasons such as permissions.
     *
     * @return bool Returns the success value of the folder being moved.
     */
    public function moveTo($newPath) 
    {
        // Rename this directory
        if(!rename($this->rootPath, $newPath))
            return false;
        
        // Clear stats cache
        clearstatcache();
        
        // Reset the root path, and rescan
        $this->rootPath = truePath($newPath) . DS;
        $this->parentDir = dirname($newPath) . DS;
        $this->_scanDir();
        
        return true;
    }
    
    /**
     * Renames the directory.
     *
     * @param string $newName The new name of the directory
     *
     * @throws \IOException Thrown if there was an error creating the new directory path
     * @throws \SecurityException Thrown if the directory could not be opened
     *   for various security reasons such as permissions.
     *
     * @return bool Returns the success value of the folder being renamed.
     */
    public function rename($newName) 
    {
        // Attempt a rename
        $newDir = truePath($this->parentDir . $newName);
        if(!rename($this->rootPath, $newDir))
            return false;
        
        // update the the path and such
        $this->rootPath = $newDir;
        $this->_scanDir();
        
        return true;
    }
    
    /**
     * Completly empties a directory of all files and subfolders
     *
     * @throws \IOException Thrown if there was an error removing a file or directory
     *
     * @return bool Returns the success value of the folder being truncated.
     */
    public function truncate() 
    {
        // Default variables
        $Error = false;
        $message = null;
        
        // Remove directories first!
        foreach($this->subdirs as $dir)
        {
            try {
                $Dir = new DirectoryInfo($this->rootPath . $dir);
                $Dir->truncate();
                rmdir($Dir->fullpath());
            }
            catch( \IOException $e ) {
                throw $e;
            }
            catch( \Exception $e ) {
                $Error = true;
                $message = 'Could not remove directory: "'. $this->rootPath . $dir .'". Exception thrown : '. $e->getMessage();
            }
            
            // Throw exception upon error
            if($Error)
                throw new \IOException($message);
        }
        
        // Now Files
        foreach($this->filelist as $f)
        {
            // Throw exception if there is an error removing a file
            if(!unlink($this->rootPath . $f) && file_exists($this->rootPath . $f))
                throw new \IOException("Could not remove file: ". $this->rootPath . $f);
        }
        
        // Clearstats cache
        clearstatcache();
        $this->_scanDir();
        return true;
    }
    
    /**
     * Creates a new sub directory
     *
     * @param string $name The basename of the subdirectory
     * @param int $chmod The folder permissions (chmod)
     *
     * @return bool Returns the success value of the folder being created.
     */
    public function createDir($name, $chmod = 0777) 
    {
        // Get current directory mask
        $oldumask = umask(0);
        $newPath = $this->parentDir . $name;
        if( !mkdir($newPath, 0777, true) )
        {
            // Add trace for debugging
            // \Debug::trace("Failed to create directory '{$path}' (chmod: $chmod)", __FILE__, __LINE__);
            return false;
        }
        
        // Return to the old file mask, and return true
        umask($oldumask);
        
        // Add the new dir to the array of dirs
        $this->subdirs[] = $name;
        return true;
    }
    
    /**
     * Removes a sub directory. This method should be used with caution as
     * it is recursive, and will delete all sub files and directories in the
     * specified folder.
     *
     * @param string $name The basename of the subdirectory
     *
     * @throws \IOException Thrown if there was an error removing a file or directory
     *
     * @return bool Returns the success value of the folder being removed.
     */
    public function removeDir($name) 
    {
        // First, truncate the directory.
        $Dir = new DirectoryInfo($this->rootPath . $name);
        if(!$Dir->truncate())
            return false;
            
        // Remove the directory now that its empty
        $result = rmdir($Dir->fullpath());
        clearstatcache();
        
        // Rescan directory
        $this->_scanDir();
        return $result;
    }
    
    /**
     * Creates a new file withing the directory.
     *
     * @param string $name The basename of the new file.
     * @param string|string[] $contents The contents to be put into the file.
     *   If an array is passed, each array index will be treated as a new line
     *   (implode("\n", $contents))
     *
     * @return bool Returns the success value of the file being created.
     */
    public function createFile($name, $contents = null) 
    {
        // Attempt to create the file
        $file = $this->rootPath . $name;
        $handle = @fopen($file, 'w+');
        if($handle)
        {
            // If contents are an array, then serialize them
            if(is_array($contents)) 
                $contents = implode("\n", $contents);
            
            // only add file contents if they are not null!
            if(!empty($contents))
                fwrite($handle, $contents);
            
            // Close the handle
            fclose($handle);
            
            // Rescan the directory
            $this->_scanDir();
            
            // Return true if we are here
            return true;
        }
        
        return false;
    }
    
    /**
     * Removes a file from the directory.
     *
     * @param string $name The basename of the new file.
     *
     * @return bool Returns the success value of the file being deleted.
     */
    public function removeFile($name) 
    {
        // Remove the file
        if(!unlink($this->rootPath . $name))
            return false;
        
        // Rescan directory
        $this->_scanDir();
        return true;
    }
    
    /**
     * Fetches the size of all files within the directory, including
     * those in all subdirectories (full recursive).
     *
     * This method will not factor in the size of files within directories
     * that cannot be opened due to permissions.
     *
     * @param bool $format Format the size into a human readable format?
     *
     * @return float|string Returns the size of all sub files recursivly
     */
    public function size($format = false)
    {
        $size = 0;
        
        // Directories first
        foreach($this->subdirs as $dir)
        {
            try {
                $Dir = new DirectoryInfo($this->rootPath . $dir);
                $size += $Dir->size();
            }
            catch( \Exception $e ) {}
        }
        
        // Now Files
        foreach($this->filelist as $f)
        {
            try {
                $File = new FileInfo($this->rootPath . $f);
                $size += $File->size();
            }
            catch( \Exception $e ) {}
        }
        
        return ($format) ? formatSize($size) : $size;
    }
    
    /**
     * Returns whether this directory is writable or not.
     *
     * @return bool
     */
    public function isWritable() 
    {
        // Fix path, and Create a tmp file
        $file = $this->rootPath . uniqid(mt_rand()) .'.tmp';
        
        // check tmp file for read/write capabilities
        $handle = @fopen($file, 'a');
        if ($handle === false) 
            return false;
        
        // Close the folder and remove the temp file
        fclose($handle);
        unlink($file);
        return true;
    }
    
    /**
     * Scans the current directory, and setting the filelist and subdir list
     * variables.
     *
     * @return void
     */
    protected function _scanDir()
    {
        // Open the directory
        $handle = @opendir($this->rootPath);
        if($handle === false) 
            throw new \SecurityException('Unable to open folder "'. $this->rootPath .'"');
        
        // Loop through each file
        while(false !== ($f = readdir($handle)))
        {
            // Skip "." and ".." directories
            if($f == "." || $f == "..") continue;

            // make sure we establish the full path to the file again
            $file = $this->rootPath . $f;
            
            // If is directory, call this method again to loop and delete ALL sub dirs.
            if( is_dir($file) ) 
                $this->subdirs[] = $f;
            else
                $this->filelist[] = $f;
        }
        
        // Close our path
        closedir($handle);
    }
    
    /**
     * When used as a string, this object returns the fullpath to the folder.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->rootPath;
    }
}