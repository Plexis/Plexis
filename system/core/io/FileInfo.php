<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/IO/FileInfo.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    DirectoryInfo
 */
namespace Core\IO;

/**
 * A File class used to preform advanced operations and provide information
 * about the file.
 *
 * @author      Steven Wilson 
 * @package     Core
 * @subpackage  IO
 */
class FileInfo
{
    /**
     * File mode OVERWRITE
     * @var int
     */
    const OVERWRITE = 0;
    
    /**
     * File mode PREPEND
     * @var int
     */
    const PREPEND = 1;
    
    /**
     * File mode APPEND
     * @var int
     */
    const APPEND = 2;
    
    /**
     * The full path to the file's parent directory
     * @var string
     */
    protected $parentDir;
    
    /**
     * The full path to the file's current location, including the filename
     * @var string
     */
    protected $filepath;
    
    /**
     * Class Constructor
     *
     * @param string $path The full path the the file
     * @param bool $create Create the file if it doesnt exist?
     *
     * @throws \IOException Thrown if the $path directory doesnt exist,
     *   $create is set to true, and there was an error creating the file.
     * @throws \FileNotFoundException If the $path file does not exist, and $create is set to false.
     * @throws \Exception Thrown if the $path is not a file at all, but rather a directory
     *
     * @return void
     */
    public function __construct($path, $create = false)
    {
        // Make sure the file exists, or we are creating a file
        if(!file_exists($path))
        {
            // Do we attempt to create?
            if(!$create)
                throw new \FileNotFoundException("File '{$path}' does not exist");
            
            // Attempt to create the file
            $handle = @fopen($file, 'w+');
            if($handle)
            {
                // Close the handle
                fclose($handle);
            }
            else
                throw new \IOException("Cannot create file '{$path}'");
        }
        elseif(!is_file($path))
        {
            throw new \Exception("'{$path}' is not a file!");
        }
        
        // Define path
        $this->filepath = $path;
        $this->parentDir = dirname($path) . DS;
    }
    
    /**
     * Returns the contents of the file
     *
     * @param bool $asArray If set to true, the contents will be returned as an array.
     *   Each array index will be a new line within the file.
     *
     * @return string|string[]
     */
    public function getContents($asArray = false) 
    {
        return ($asArray) ? file($this->filepath) : file_get_contents($this->filepath);
    }
    
    /**
     * Sets or adds contents of the file
     *
     * @param string|string[] $contents The contents to be stored in the file. 
     *   If an array is passed, each array index will be treated as a new line within 
     *   the file.
     * @param int $mode The write mode for the contents (See class constants).
     *
     * @return bool Returns TRUE on success, or FALSE on failure.
     */
    public function putContents($contents, $mode = self::OVERWRITE) 
    {
        // If array, convert to string
        if(is_array($contents))
            $contents = implode(PHP_EOL, $contents);
        
        // Are we appending or prepending?
        if($mode != self::OVERWRITE)
        {
            $c = $this->getContents();
            $contents = ($mode == self::APPEND) ? $c . $contents : $contents . $c;
        }
        
        return (file_put_contents($this->filepath, $contents) !== false);
    }
    
    /**
     * Returns the base file name
     *
     * @return string
     */
    public function name()
    {
        return basename($this->filepath);
    }
    
    /**
     * Returns the full path to the file, including the file name
     *
     * @return string
     */
    public function filepath()
    {
        return $this->filepath;
    }
    
    /**
     * Returns the directory that the file is located in
     *
     * @param bool $asString If set to true, The string path will be
     *   returned, otherwise the DirectoryInfo object of the dir will be returned
     *
     * @return string|DirectoryInfo
     */
    public function getParentDir($asString = false)
    {
        return ($asString) ? $this->parentDir : new DirectoryInfo($this->parentDir);
    }
    
    /**
     * Moves the file to a new location.
     *
     * The old file will not be removed until the new file is created successfully.
     *
     * @param string $newPath The full path to move the file to
     * @param string $newName If set, the file will also be renamed in its new location
     *
     * @throws \IOException Thrown if there was an error creating the new directory path
     * @throws \SecurityException Thrown if the $newPath directory could not be opened
     *   for various security reasons such as permissions.
     *
     * @return bool Returns true on success, false otherwise
     */
    public function moveTo($newPath, $newName = false) 
    {
        // Make sure we have a filename
        if(empty($newName))
            $newName = basename($this->filepath);
            
        // Correct new path
        $newPath = truePath($newPath) . DS;
        
        $Dir = new DirectoryInfo($newPath, true);
        if(!$Dir->createFile($newName, $this->getContents()))
            return false;
        
        // Delete old file, and re-set current file paths
        unlink($this->filepath);
        $this->filepath = $newPath . $newName;
        $this->parentDir = $newPath;
        return true;
    }
    
    /**
     * Creates a copy of the file to the specified file location
     *
     * @param string $fileName The name of the file to copy to
     * @param string $newPath The path to the new file if not in the current
     *   file directory.
     *
     * @return bool Returns true on success, false otherwise
     */
    public function copyTo($fileName, $newPath = false) 
    {
        // Make sure we have a correct path
        $newPath = (empty($newPath)) ? $this->parentDir : rtrim($newPath, DS) . DS;
        
        // return the copy result
        return copy($this->filepath, $newPath . $fileName);
    }
    
    /**
     * Renames the file.
     *
     * @param string $newName The new name of the file
     *
     * @return bool Returns true on success, false otherwise
     */
    public function rename($newName) 
    {
        // Attempt to rename
        if(!rename($this->filepath, $this->parentDir . $newName))
            return false;
            
        // Reset file path
        $this->filepath = $this->parentDir . $newName;
        return true;
    }
    
    /**
     * Completly removes all contents of the file
     *
     * @return bool Returns true on success, false otherwise
     */
    public function truncate() 
    {
        $f = @fopen($this->filepath, "r+");
        if ($f !== false) 
        {
            ftruncate($f, 0);
            fclose($f);
            return true;
        }
        return false;
    }
    
    /**
     * Gets last amodification time of file
     *
     * @return int|bool Returns the time the file was last modified, 
     * or FALSE on failure. The time is returned as a Unix timestamp.
     */
    public function modified() 
    {
        return filemtime($this->filepath);
    }
    
    /**
     * Gets last access time of file
     *
     * @return int|bool Returns the time the file was last accessed, 
     * or FALSE on failure. The time is returned as a Unix timestamp.
     */
    public function accessed() 
    {
        return fileatime($this->filepath);
    }
    
    /**
     * Gets the size for the file
     *
     * @param bool $format Format the file size to human readable format?
     * @param bool $gt2gb Do we think this file to be over 2 GB? This is used to get
     *   an accurate filesize via the command line on 32 bit systems.
     *
     * @return float|string|bool Returns false on failure, a float if $format is false, or
     *   a string if $format is true
     */
    public function size($format = false, $gt2gb = false) 
    {
        // Get most accurate filesize based on operating system
        $total_size = '0';
        $is64Bit = (PHP_INT_MAX > 2147483647);
        
        // If we suspect the file being over 2 GB on a 32 bit system, use command line
        if($gt2gb && !$is64Bit)
        {
            // Get file zize
            $isWindows = (substr(strtoupper(PHP_OS), 0, 3) === 'WIN');
            $total_size = ($isWindows)
                ? exec("for %v in (\"". $this->filepath ."\") do @echo %~zv") // Windows
                : shell_exec("stat -c%s " . escapeshellarg($this->filepath)); // Linux
            
            // If we failed to get a size, we take extreme messures
            if(!$total_size || !is_numeric($total_size))
            {
                if($isWindows)
                {
                    // Check for windows COM
                    if(class_exists("COM", false)) 
                    {
                        $fsobj = new COM('Scripting.FileSystemObject');
                        $f = $fsobj->GetFile(truePath($this->filepath));
                        $total_size = (float) $f->Size;
                    } 
                    else 
                    {
                        return false;
                    }
                }
                else
                {
                    $total_size = trim(exec("perl -e 'printf \"%d\n\",(stat(shift))[7];' ". $this->filepath));
                    if(!$total_size || !is_numeric($total_size))
                        return false;
                }
            }
        }
        
        // Just to make sure, try and return the filesize() if nothing else
        if($total_size == '0' || !$total_size || !is_numeric($total_size)) 
            $total_size = (float) @filesize($this->filepath);
        return ($format == true) ? formatSize($total_size) : (float) $total_size;
    }
    
    /**
     * Fetches or sets the permissions of the file
     *
     * @param int $ch The permission level to set on the file (chmod).
     *   If left unset, the current chmod will be returned.
     *
     * @return int|bool Returns the current file chmod if $ch is left null,
     *   otherwise, returns the success value of setting the permissions.
     */
    public function chmod($ch = null) 
    {
        if(empty($ch))
            return fileperms($this->filepath);
            
        return chmod($this->filepath, $ch);
    }
    
    /**
     * Fetches the mime type of this file.
     *
     * @return string|string[] Returns an array if there is more then
     *   1 mime type (Ordered by most common to least), or a string if
     *   there is only 1 mime type. Returns false if there is no entry
     *   for the given file extenstion
     */
    public function mimeType() 
    {
        return Library\Mime::GetType($this->ext());
    }
    
    /**
     * Gets the file's extension
     *
     * @return string
     */
    public function ext() 
    {
        return pathinfo($this->filepath, PATHINFO_EXTENSION);
    }
    
    /**
     * Returns whether this file is writable or not.
     *
     * @return bool
     */
    public function isWritable() 
    {
        // Attempt to open the file, and read contents
        $handle = @fopen($this->filepath, 'a');
        if($handle === false) 
            return false;
        
        // Close the file, return true
        fclose($handle);
        return true;
    }
    
    /**
     * Returns whether this file is readable or not.
     *
     * @return bool
     */
    public function isReadable()
    {
        // Attempt to open the file, and read contents
        $handle = @fopen($this->filepath, 'r');
        if($handle === false)
            return false;
        
        // Close the file, return true
        fclose($handle);
        return true;
    }
    
    /**
     * When used as a string, this object returns the fullpath to the file.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->filepath;
    }
}