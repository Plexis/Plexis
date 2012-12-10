<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/ErrorHandler.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    ErrorHandler
 */
namespace Core;

/**
 * Responsible for handling all errors, and execptions, and displaying 
 * an error page
 *
 * @author      Steven Wilson 
 * @package     Core
 */
class ErrorHandler
{
    /**
     * Class Constructor (Internally called)
     *
     * @return void
     */
    public static function Init()
    {
        // Proccess error reporting levels and such
    }
    
    /**
     * This method is used to set a custom class and method for displaying errors
     *
     * @param string $controller The controller class name
     * @param string $action The method to the classname for displaying the error
     * @return void
     */
    public static function SetErrorHandler($controller, $action)
    {
    
    }
    
    /**
     * Main method for showing an error. Not garunteed to display the error, just
     * depends on the users error reporting level.
     *
     * @param int $lvl Error level. the error levels share the php constants error levels
     * @param string $message The error message
     * @param string $file The filename in which the error was triggered from
     * @param int $line The line number in which the error was triggered from
     * @return void
     */
    public static function TriggerError($lvl, $message, $file, $line)
    {
        self::DisplayError($lvl, $message, $file, $line);
    }
    
    /**
     * Same method as TriggerError, except this method is called by php internally
     *
     * @param int $lvl Error level. the error levels share the php constants error levels
     * @param string $message The error message
     * @param string $file The filename in which the error was triggered from
     * @param int $line The line number in which the error was triggered from
     * @return void
     */
    public static function HandlePHPError($lvl, $message, $file, $line)
    {
        self::DisplayError($lvl, $message, $file, $line, true);
    }
    
    /**
     * Main method for handling exceptions
     *
     * @param \Exception $e The thrown exception
     * @return void
     */
    public static function HandleException($e)
    {
        self::DisplayError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
    }
    
    /**
     * Displays the error screen
     *
     * @param int $lvl Error level. the error levels share the php constants error levels
     * @param string $message The error message
     * @param string $file The filename in which the error was triggered from
     * @param int $line The line number in which the error was triggered from
     * @param bool $php Php thrown error or exception?
     * @return void
     */
    protected static function DisplayError($lvl, $message, $file, $line, $php = false)
    {
        // Clear out all the old junk so we don't get 2 pages all fused together
        if(ob_get_length() != 0) ob_clean();
        
        // Will make this fancy later
        $mode = ($exception instanceof \Exception) ? "Exception" : "Error";
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