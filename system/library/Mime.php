<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Copyright:    Copyright (c) 2011-2012, Plexis Dev Team
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Mime
| ---------------------------------------------------------------
|
| An HTTP mime parser class
|
*/
namespace Library;

class Mime
{
    protected $mimeTypes = array(
        // Common Files
        'htm' => 'text/html',
        'html' => 'text/html',
        'xhtml'	=> array('application/xhtml+xml', 'application/xhtml', 'text/xhtml'),
        'xhtml-mobile'	=> 'application/vnd.wap.xhtml+xml',
        'xml' => array('application/xml', 'text/xml'),
        'css' => 'text/css',
        'js' => 'text/javascript',
        'javascript' => 'text/javascript',
        'rss' => 'application/rss+xml',
        'json' => 'application/json',
        'csv' => array('text/csv', 'application/vnd.ms-excel', 'text/plain'),
        'file' => 'multipart/form-data',
        'php' => 'text/x-php',
        
        // Common Images
        'gif' => 'image/gif',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'ico' => array('image/x-icon', 'image/vnd.microsoft.icon'),
        
        // Binaries
        'exe' => 'application/octet-stream',
        'bin' => 'application/octet-stream',
        'sh' => 'application/x-sh',
        
        // Compressions
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'gtar' => 'application/x-gtar',
        'gz' => 'application/x-gzip',
        'bz2' => 'application/x-bzip',
        '7z' => 'application/x-7z-compressed',
        'tar' => 'application/x-tar',
        
        // Fonts
        'otf' => 'font/otf',
        'ttf' => 'font/ttf',
        
        // Audio / Video
        'mp2' => 'audio/mpeg',
        'mp3' => 'audio/mpeg',
        'mpga' => 'audio/mpeg',
        'ogg' => 'audio/ogg',
        'oga' => 'audio/ogg',
        'spx' => 'audio/ogg',
        'ra' => 'audio/x-realaudio',
        'ram' => 'audio/x-pn-realaudio',
        'rm' => 'audio/x-pn-realaudio',
        'rpm' => 'audio/x-pn-realaudio-plugin',
        'wav' => 'audio/x-wav',
        'aac' => 'audio/aac',
        'flac' => 'audio/flac',
        'avi' => array('video/x-msvideo', 'video/avi'),
        'mov' => 'video/quicktime',
        'mpe' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'qt' => 'video/quicktime',
        'ogv' => 'video/ogg',
        'webm' => 'video/webm',
        'mp4' => 'video/mp4',
        'flv' => 'video/x-flv',
        
        // Text
        'ics' => 'text/calendar',
        'rtf' => 'text/rtf',
        'rtx' => 'text/richtext',
        'tsv' => 'text/tab-separated-values',
        'tpl' => 'text/template',
        'txt' => 'text/plain',
        'text' => 'text/plain',
        
        // Non-Common Images
        'ief' => 'image/ief',
        'pbm' => 'image/x-portable-bitmap',
        'pgm' => 'image/x-portable-graymap',
        'pnm' => 'image/x-portable-anymap',
        'ppm' => 'image/x-portable-pixmap',
        'ras' => 'image/cmu-raster',
        'rgb' => 'image/x-rgb',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'xbm' => 'image/x-xbitmap',
        'xpm' => 'image/x-xpixmap',
        'xwd' => 'image/x-xwindowdump',
        
        // Adobe
        'pdf' => 'application/pdf',
        'psd' => array('image/vnd.adobe.photoshop', 'application/octet-stream'),
        'swf' => 'application/x-shockwave-flash',
        
        // Microsoft Documents
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlc' => 'application/vnd.ms-excel',
        'xll' => 'application/vnd.ms-excel',
        'xlm' => 'application/vnd.ms-excel',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xlw' => 'application/vnd.ms-excel',
        'pot' => 'application/vnd.ms-powerpoint',
        'pps' => 'application/vnd.ms-powerpoint',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'ppz' => 'application/vnd.ms-powerpoint',
    );
    
/*
| ---------------------------------------------------------------
| Method: GetType()
| ---------------------------------------------------------------
|
| Returns a mime type for the provided file extension
|
| @Param: (String) $ext - The file extension
| @Return: (Array | String) Returns an array if there is more then
|   1 mime type (Ordered by most common to least), or a string if
|   there is only 1 mime type. Returns false if there is no entry
|   for the given file extenstion
|
*/ 
    public static function GetType($ext)
    {
        if(array_key_exists($ext, self::$mimeTypes))
            return self::$mimeTypes[$ext];
            
        return false;
    }
    
/*
| ---------------------------------------------------------------
| Method: SetType()
| ---------------------------------------------------------------
|
| Sets a mime type for the provided file extension
|
| @Param: (String) $ext - The file extension
| @Param: (String | Array) $value - A string or array of mime types
| @Return: (None)
|
*/ 
    public static function SetType($ext, $value)
    {
        self::$mimeTypes[$ext] = $value;
    }
}