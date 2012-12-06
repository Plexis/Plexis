<?php
/* 
| --------------------------------------------------------------
| Plexis Core
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Copyright:    Copyright (c) 2011-2012, Plexis Dev Team
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: ErrorHandler
| ---------------------------------------------------------------
|
| This class is responsible for handling all errors, displaying and
| logging.
|
*/
namespace Core;

class ErrorHandler
{
    public static function Init()
    {
        // Proccess error reporting levels and such
    }
    
    public static function SetErrorHandler($controller, $action)
    {
    
    }
    
    public static function TriggerError($lvl, $message, $file, $line)
    {
        self::DisplayError($lvl, $message, $file, $line);
    }
    
    public static function HandlePHPError($lvl, $message, $file, $line)
    {
        self::DisplayError($lvl, $message, $file, $line, true);
    }
    
    public static function HandleException($e)
    {
        self::DisplayError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), false, true);
    }
    
    protected static function DisplayError($lvl, $message, $file, $line, $php = false, $exception = false)
    {
        // Clear out all the old junk so we don't get 2 pages all fused together
        if(ob_get_length() != 0) ob_clean();
        
        // Will make this fancy later
        $mode = ($exception == true) ? "Exception" : "Error";
        $title = ($php == true) ? "PHP {$mode}: " : "{$mode} Thrown: ";
        
        // We wont use a view here because we might not have the Library namespace registered in the autoloader
        $page = file_get_contents( path(SYSTEM_PATH, "errors", "general_error.php") );
        $page = str_replace('{TITLE}', $title, $page);
        $page = str_replace('{MESSAGE}', $message, $page);
        $page = str_replace('{FILE}', $file, $page);
        $page = str_replace('{LINE}', $line, $page);
        
        // Prepare response
        Response::StatusCode(500);
        Response::Body($page);
        Response::Send();
        die;
    }
}

ErrorHandler::Init();