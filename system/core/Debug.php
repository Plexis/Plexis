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
namespace Core;

class Debug
{
    // Error specific variables
    protected $ErrorNo;         // Error Number
    protected $ErrorMessage;    // Error message
    protected $ErrorFile;       // Error file
    protected $ErrorLine;       // Error line.
    protected $ErrorLevel;      // Error Level Text.
    
    // Our log error level.
    protected $LogLevel;
    
    // Our sites error level.
    protected $Environment;

    // Silent Mode
    protected $silence = false;
    
    // Array of trac and system messages to be logged
    protected $traceLogs = array();
    protected $systemLogs = array();
    
    // Have we already wrote our logs?
    protected $logged = false;
    
    // Our URL info
    protected $urlInfo;
    
    // Ajax Request?
    protected $isAjax;


/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct()
    {
        // Set our error reporting
        $this->config = load_class('Config');
        $this->LogLevel = $this->config->get('log_level', 'Core');
        $this->Environment = $this->config->get('environment', 'Core');
        
        // Get our URL info
        $this->urlInfo = load_class('Router')->get_url_info();
        $this->isAjax = load_class('Input')->is_ajax();
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
    public function trigger_error($errno, $message = '', $file = '', $line = 0)
    {
        // Fill error specific attributes
        $this->ErrorNo = $errno;
        $this->ErrorLevel = $this->error_string($errno);
        $this->ErrorMessage = $message;
        $this->ErrorFile = $file;
        $this->ErrorLine = $line;
        
        /* 
            Get our severity:
            1 = Less then error level... Passable
            2 = Error that can be silenced
            3 = Fetal Error, None silencable
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

        // If we are silent, and the error is Non-Fetal... then be silent
        if($severity == 3 || !$this->silence)
        {
            // Log error based on error log level
            if($this->LogLevel != 0) $this->log_error();
        
            // Only build the error page when it's fatal, or a development Env.
            if( $this->Environment == 2 || $severity > 1 )
            {
               $this->output_error();
               if( !$this->logged ) $this->write_logs();
            }
        }
    }

/*
| ---------------------------------------------------------------
| Method: log_error()
| ---------------------------------------------------------------
|
| Logs the error message in the error log
|
*/
    protected function log_error($was_silenced = false)
    {
        // Get our site url
        $url = $this->urlInfo;
        $DB = load_class('Loader')->database('DB', false, true);
        
        // Only log in the database if database is connectable
        if(is_object($DB))
        {
            // Attempt to insert the error in the database
            $data = array(
                'level' => $this->ErrorLevel,
                'string' => $this->ErrorMessage,
                'file' => $this->ErrorFile,
                'line' => $this->ErrorLine,
                'url' => $url['site_url'] ."/". $url['uri'],
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
            $err_message .= "| Error Level: ".$this->ErrorLevel . PHP_EOL;
            $err_message .= "| Message: ".$this->ErrorMessage . PHP_EOL; 
            $err_message .= "| Reporting File: ".$this->ErrorFile . PHP_EOL;
            $err_message .= "| Error Line: ".$this->ErrorLine . PHP_EOL;
            $err_message .= "| Error Displayed: ". ($was_silenced == true) ? "No" : "Yes". PHP_EOL;
            $err_message .= "| URL When Error Occured: ". $url['site_url'] ."/". $url['uri'] . PHP_EOL;
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
    public function show_error($type)
    {
        // Clear out all the old junk so we don't get 2 pages all fused together
        if(ob_get_level() != 0) ob_end_clean();

        // Get our site url
        $site_url = $this->urlInfo['site_url'];
        
        // Include error page
        include(SYSTEM_PATH . DS . 'errors' . DS . 'error_'. $type .'.php');
        
        // Kill the script
        die();
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
    public function trace($message, $file = 'none', $line = 'none')
    {
        $this->traceLogs[] = array('message' => $message, 'file' => $file, 'line' => $line);
    }
    
/*
| ---------------------------------------------------------------
| Method: backtrace()
| ---------------------------------------------------------------
|
| Returns all traced messages in an array
|
*/
    public function backtrace()
    {
        return $this->traceLogs;
    }
    
/*
| ---------------------------------------------------------------
| Method: log()
| ---------------------------------------------------------------
|
| Logs a message for file writting
|
*/
    public function log($type, $message)
    {
        // Determine if the user wants this logged
        switch( $this->LogLevel )
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
    public function silent_mode($silent = true)
    {
        $this->silence = $silent;
        return TRUE;
    }
    
/*
| ---------------------------------------------------------------
| Method: output_error()
| ---------------------------------------------------------------
|
| Builds the error page and displays it
|
*/
    protected function output_error()
    {
        // Clear out all the old junk so we don't get 2 pages all fused together
        if(ob_get_level() != 0) ob_end_clean();
        
        // If this is an ajax request, popup a dialog rather then error page.
        if($this->isAjax)
        {
            // Only display ajax errors if they are severe!
            if($this->ErrorNo != E_STRICT && $this->ErrorNo != E_DEPRECATED)
            {
                $string = $this->ErrorLevel;
                $file = str_replace( ROOT . DS, '', $this->ErrorFile );
                echo json_encode(
                    array(
                        'success' => false,
                        'php_error' => true,
                        'php_error_data' => array(
                            'level' => str_replace('PHP ', '', $string),
                            'message' => $this->ErrorMessage,
                            'file' => $file,
                            'line' => $this->ErrorLine
                        ),
                        'message' => '['. $string .'] '. $this->ErrorMessage ." in File: $file, on Line: ". $this->ErrorLine,
                        'data' => '['. $string .'] '. $this->ErrorMessage ." in File: $file, on Line: ". $this->ErrorLine,
                        'type' => 'error'
                    )
                );
            }
        }
        else
        {
            // Get our site url
            $site_url = $this->urlInfo['site_url'];
            
            // Capture the orig_settingslate using Output Buffering, file depends on Environment
            ob_start();
                $file = SYSTEM_PATH . DS . 'errors' . DS . 'general_error.php';
                if(file_exists($file)) include($file);
                $page = ob_get_contents();
            @ob_end_clean();
            
            // alittle parsing
            $search = array('{ERROR_LEVEL}', '{MESSAGE}', '{FILE}', '{LINE}');
            $replace = array($this->ErrorLevel, $this->ErrorMessage, $this->ErrorFile, $this->ErrorLine);
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
    public function error_string($level)
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
// EOF