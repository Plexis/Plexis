<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Library/ZipArchive.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Template
 */
namespace Library;

// Import a few classes so we dont have to specify namespaces
use \Core\IO\DirectoryInfo;
use \DirectoryNotFoundException;
use \IOException;

/**
 * A Recursive directory zipping class
 *
 * @author      Steven Wilson 
 * @package     Library
 */
class ZipArchive
{
    /**
     * Main method used to Zip a directory recursively
     *
     * @param string $source The source directory path we are zipping
     * @param string $destination The full path to the Ziparchive, including filename
     *   and extension.
     *
     * @return \ZipArchive Returns the opened ZipArchive object of the newely
     *   created Zip file
     */
    public static function ZipDirectory($source, $destination)
    {
        // We Need the Zip extension installed!
        if(!extension_loaded('zip'))
            return false;
            
        // Get our real paths to the destination and source folders
        $source = truePath($source);
        $dest = truePath($destination);
        $destDirname = dirname($dest);
        
        // Get our Directory objects of both the Source and Dest dirs
        $DestDir = new DirectoryInfo($destDirname, true);
        $SourceDir = new DirectoryInfo($source);
        
        // Make sure the Destination directory is writable!
        if(!$DestDir->isWritable())
            throw new IOException('Destination must be a writable directory.');
        
        // Create the zip file
        $ZipFile = $DestDir->createFile(basename($dest));

        $Zip = new \ZipArchive();
        $Zip->open($dest, \ZipArchive::CREATE);
        
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file)
        {
            if( in_array(substr($file, strrpos($file, DS)+1), array('.', '..')) )
                continue;

            $relative = substr($file, strlen($source));
            if (is_dir($file) === true)
            {
                // Add directory
                $added = $Zip->addEmptyDir(trim($relative, "\\/"));
                if (!$added)
                    throw new \Exception('Unable to add directory named: ' . trim($relative, "\\/"));
            }
            else if (is_file($file) === true)
            {
                // Add file
                $added = $Zip->addFromString(trim($relative, "\\/"), file_get_contents($file));
                if (!$added)
                {
                    throw new \Exception('Unable to add file named: ' . trim($relative, "\\/"));
                }
            }
        }

        return $Zip;
    }
}