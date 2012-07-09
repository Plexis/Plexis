<?php
/* 
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Debug
| ---------------------------------------------------------------
|
| Main debug / error handler for the Cms
|
*/

class Debug
{
    // Error specific variables
    protected static $ErrorNo;         // Error Number
    protected static $ErrorMessage;    // Error message
    protected static $ErrorFile;       // Error file
    protected static $ErrorLine;       // Error line.
    protected static $ErrorLevel;      // Error Level Text.
    
    // Our log error level.
    protected static $LogLevel;
    
    // Our sites error level.
    protected static $Environment;
    
    // Catch Fatal errors variable
    protected static $catchFatalErrors;

    // Silent Mode
    protected static $silence = false;
    
    // Array of trac and system messages to be logged
    protected static $traceLogs = array();
    protected static $systemLogs = array();
    
    // Have we already wrote our logs?
    protected static $logged = false;
    
    // Our URL info
    protected static $urlInfo;
    
    // Ajax Request?
    protected static $ajaxRequest;
    
    // Output sent to browser?
    protected static $outputSent = false;


/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public static function Init()
    {
        // Load the config class
        $Config = load_class('Config');
        
        // Fill our error reporting variables
        self::$LogLevel = $Config->get('log_level', 'Core');
        self::$Environment = $Config->get('environment', 'Core');
        self::$catchFatalErrors = $Config->get('catch_fatal_errors', 'Core');
        
        // Get our URL info
        self::$urlInfo = load_class('Router')->get_url_info();
        self::$ajaxRequest = load_class('Input')->is_ajax();
        
        // Add this to the trace
        self::trace('Debug class initialized successfully', __FILE__, __LINE__);
    }
    
/*
| ---------------------------------------------------------------
| Destructor
| ---------------------------------------------------------------
|
*/
    public static function Shutdown()
    {
        // Generate the debug log
        $error = error_get_last();
        
        // If we have an error on shutdown, that means we never caught it :O ... its Fatal
        if(is_array($error) && self::$catchFatalErrors == 1 && !self::$outputSent)
        {
            // Trigger
            return self::trigger_error($error['type'], $error['message'], $error['file'], $error['line']);
        }
        
        // Add this to the trace
        self::trace('Everything complete... Shutting down', __FILE__, __LINE__);
        
        // Generate trace logs
        if( !self::$logged )
        {
            self::write_debuglog('debug');
            self::$logged = true;
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: trigger_error()
| ---------------------------------------------------------------
|
| Main error handler. Triggers, logs, and shows the error message
|
| @Param: (Int)     $errno - The error number
| @Param: (String)  $message - Error message
| @Param: (String)  $file - The file reporting the error
| @Param: (Int)     $line - Error line number
| @Param: (Int)     $errno - Error number
| @Param: (Array)   $backtrace - Backtrace information if any
|
*/
    public static function trigger_error($errno, $message = '', $file = '', $line = 0)
    {
        // Return false if there is no error code
        if(!$errno) return false;
        
        // Fill error specific attributes
        self::$ErrorNo = $errno;
        self::$ErrorLevel = self::error_string($errno);
        self::$ErrorMessage = $message;
        self::$ErrorFile = $file;
        self::$ErrorLine = $line;
        
        /* 
            Get our severity:
            1 = Less then error level... Passable
            2 = Error that can be silenced
            3 = Fatal Error, None silencable
        */
        switch($errno)
        {
            case E_ERROR:
            case E_USER_ERROR:
                $severity = 2;
                break;
            case E_NOTICE:
            case E_WARNING:
            case E_DEPRECATED:
            case E_STRICT:
            case E_USER_WARNING:    
            case E_USER_NOTICE:
                $severity = 1;
                break;
            default:
                $severity = 3;
                break;
        }

        // If we are silent, and the error is Non-Fatal... then be silent
        if($severity == 3 || !self::$silence)
        {
            // Add this to the trace
            self::trace('Error triggered: '. $message .'; File: '. $file .'['. $line .']', __FILE__, __LINE__);
            
            // Log error based on error log level
            if(self::$LogLevel != 0) self::log_error();
        
            // Only build the error page when it's fatal, or a development Env.
            if( self::$Environment == 2 || $severity > 1 )
            {
                // Generate trace logs
                if( !self::$logged )
                {
                    self::write_debuglog('debug');
                    self::$logged = true;
                }
        
                // Output error
                self::output_error();
            }
        }
        
        // Don't execute PHP internal error handler
        return true;
    }

/*
| ---------------------------------------------------------------
| Method: log_error()
| ---------------------------------------------------------------
|
| Logs the error message in the error log
|
*/
    protected static function log_error($was_silenced = false)
    {
        // Load the database
        $DB = load_class('Loader')->database('DB', false, true);
        
        // Only log in the database if database is connectable
        if(is_object($DB))
        {
            // Attempt to insert the error in the database
            $data = array(
                'level' => self::$ErrorLevel,
                'string' => self::$ErrorMessage,
                'file' => self::$ErrorFile,
                'line' => self::$ErrorLine,
                'url' => self::$urlInfo['site_url'] ."/". self::$urlInfo['uri'],
                'remote_ip' => $_SERVER['REMOTE_ADDR'],
                'time' => time(),
                'backtrace' => null
            );
            $DB->insert('pcms_error_logs', $data);
        }
        else
        {
            // Create our log message
            $err_message =  "| Logging started at: ". date('Y-m-d H:i:s') . PHP_EOL;
            $err_message .= "| Error Level: ". self::$ErrorLevel . PHP_EOL;
            $err_message .= "| Message: ". self::$ErrorMessage . PHP_EOL; 
            $err_message .= "| Reporting File: ". self::$ErrorFile . PHP_EOL;
            $err_message .= "| Error Line: ". self::$ErrorLine . PHP_EOL;
            $err_message .= "| Error Displayed: ". ($was_silenced == true) ? "No" : "Yes". PHP_EOL;
            $err_message .= "| URL When Error Occured: ". self::$urlInfo['site_url'] ."/". self::$urlInfo['uri'] . PHP_EOL;
            $err_message .= "--------------------------------------------------------------------". PHP_EOL . PHP_EOL;

            // Write in the log file, the very long message we made
            $log = @fopen(SYSTEM_PATH . DS . 'logs' . DS . 'php_errors.log', 'a');
            @fwrite($log, $err_message);
            @fclose($log);
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: show_error()
| ---------------------------------------------------------------
|
| Shows a special error page like a 503 (Forbidden) of 404 page
|
| @Param: (Numeric) $type - The type of error page such as 404.
|
*/
    public static function show_error($type)
    {
        // Clear out all the old junk so we don't get 2 pages all fused together
        if(ob_get_level() != 0) ob_end_clean();

        // Get our site url
        $site_url = self::$urlInfo['site_url'];
        
        // Include error page
        include(SYSTEM_PATH . DS . 'errors' . DS . 'error_'. $type .'.php');
        
        // Kill the script
        die();
    }
    
/*
| ---------------------------------------------------------------
| Method: output_sent()
| ---------------------------------------------------------------
|
| Callback method for the template system to prevent display of
| future errors
|
*/
    public static function output_sent()
    {
        self::$outputSent = true;
        self::trace('Page compiled and sent to browser', __FILE__, __LINE__);
    }
    
/*
| ---------------------------------------------------------------
| Method: trace()
| ---------------------------------------------------------------
|
| Creates a trace log for debugging purposes
|
| @Param: (String) $message - The trace message
| @Param: (String) $file - The file in which this trace comes from
| @Param: (Int) $line - The line in the $file calling this trace
|
*/
    public static function trace($message, $file = 'none', $line = 'none')
    {
        self::$traceLogs[] = array('message' => $message, 'file' => $file, 'line' => $line);
    }
    
/*
| ---------------------------------------------------------------
| Method: backtrace()
| ---------------------------------------------------------------
|
| Returns all traced messages in an array
|
*/
    public static function backtrace()
    {
        return self::$traceLogs;
    }
    
/*
| ---------------------------------------------------------------
| Method: write_debuglog()
| ---------------------------------------------------------------
|
| Generates the debug log
|
*/
    public static function write_debuglog($name = null)
    {
        // Do we have a custom name?
        $name = ($name == null) ? 'debug_'. time() : $name;
        
        // Make sure this isnt a ajax request
        // if(self::$ajaxRequest) return;
        
        // Build the xml
        $string = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\r\n<debug>\r\n";
        $string .= "\t<info>\r\n";
        $string .= "\t\t<cms_version>". CMS_VERSION ."</cms_version>\r\n";
        $string .= "\t\t<cms_build>". CMS_BUILD ."</cms_build>\r\n";
        $string .= "\t\t<url>". self::$urlInfo['site_url'] ."/". self::$urlInfo['uri'] ."</url>\r\n";
        $string .= "\t\t<generated>". date("F j, Y, g:i a", time()) ."</generated>\r\n";
        $string .= "\t</info>\r\n";
        
        // Loop through and add the traces
        foreach(self::$traceLogs as $key => $trace)
        {
            $string .= "\t<trace id=\"". ($key + 1) ."\">\r\n";
            $string .= "\t\t<message>". $trace['message'] ."</message>\r\n";
            $string .= "\t\t<file>". $trace['file'] ."</file>\r\n";
            $string .= "\t\t<line>". $trace['line'] ."</line>\r\n";
            $string .= "\t</trace>\r\n";
        }
        $string .= '</debug>';
        file_put_contents( path(SYSTEM_PATH, 'logs', 'debug', $name .'.xml'), $string );
    }
    
/*
| ---------------------------------------------------------------
| Method: log()
| ---------------------------------------------------------------
|
| Logs a message for file writting
|
*/
    public static function log($type, $message)
    {
        // Determine if the user wants this logged
        switch( self::$LogLevel )
        {
            case 0:
                return;
            case 1:
                if($type != 'error') return;
                break;
            case 2:
                if($type == 'info') return;
                break;
        }
        
        // Write in the log file, the very long message we made
        $message = "[".date('Y-m-d H:i:s') ."] ". ucfirst($type) .": ". $message . PHP_EOL;
        $log = @fopen( SYSTEM_PATH . DS . 'logs' . DS . 'system_logs.log', 'a' );
        if($log)
        {
            @fwrite($log, $message);
            @fclose($log);
        }
    }
    
/*
| ---------------------------------------------------------------
| Method: silent_mode()
| ---------------------------------------------------------------
|
| Enable / disable error reporting (except for fatal errors)
|
*/
    public static function silent_mode($silent = true) 
    {
        self::$silence = $silent;
    }
    
/*
| ---------------------------------------------------------------
| Method: output_error()
| ---------------------------------------------------------------
|
| Builds the error page and displays it
|
*/
    protected static function output_error()
    {
        // Clear out all the old junk so we don't get 2 pages all fused together
        if(ob_get_level() != 0) ob_end_clean();
        
        // If this is an ajax request, popup a dialog rather then error page.
        if(self::$ajaxRequest)
        {
            // Only display ajax errors if they are severe!
            if(self::$ErrorNo != E_STRICT && self::$ErrorNo != E_DEPRECATED)
            {
                $string = self::$ErrorLevel;
                $file = str_replace( ROOT . DS, '', self::$ErrorFile );
                echo json_encode(
                    array(
                        'success' => false,
                        'php_error' => true,
                        'php_error_data' => array(
                            'level' => str_replace('PHP ', '', $string),
                            'message' => self::$ErrorMessage,
                            'file' => $file,
                            'line' => self::$ErrorLine
                        ),
                        'message' => '['. $string .'] '. self::$ErrorMessage ." in File: $file, on Line: ". self::$ErrorLine,
                        'data' => '['. $string .'] '. self::$ErrorMessage ." in File: $file, on Line: ". self::$ErrorLine,
                        'type' => 'error'
                    )
                );
            }
        }
        else
        {
            // Get our site url
            $site_url = self::$urlInfo['site_url'];
            
            // Capture the orig_settingslate using Output Buffering, file depends on Environment
            ob_start();
                $file = SYSTEM_PATH . DS . 'errors' . DS . 'general_error.php';
                if(file_exists($file)) include($file);
                $page = ob_get_contents();
            @ob_end_clean();
            
            // alittle parsing
            $search = array('{ERROR_LEVEL}', '{MESSAGE}', '{FILE}', '{LINE}');
            $replace = array(self::$ErrorLevel, self::$ErrorMessage, self::$ErrorFile, self::$ErrorLine);
            $page = str_replace($search, $replace, $page);
            
            // Spit the page out
            echo $page;
        }
        
        // Kill the script
        die();
    }
    
/*
| ---------------------------------------------------------------
| Method: error_string()
| ---------------------------------------------------------------
|
| Turns an error number to a string
|
*/
    public static function error_string($level)
    {
        switch($level)
        {
            case E_ERROR: $string = 'PHP Error'; break;
            case E_PARSE: $string = 'Parse Error'; break;
            case E_USER_ERROR: $string = 'PHP Error'; break;
            case E_NOTICE:  $string = 'PHP Notice'; break;
            case E_WARNING: $string = 'PHP Warning'; break;
            case E_DEPRECATED: $string = 'Depreciated'; break;
            case E_STRICT:  $string = 'PHP Strict'; break;
            case E_USER_WARNING: $string = 'PHP Warning'; break;
            case E_USER_NOTICE: $string = 'PHP Notice'; break;
            default: $string = 'PHP Fatal Error ['. $level .']'; break;
        }
        return $string;
    }
}

// Initialize the debug class
Debug::Init();

// Register the server to process errors with the this class
set_error_handler('Debug::trigger_error', E_ALL | E_STRICT);
register_shutdown_function('Debug::Shutdown');
// EOF