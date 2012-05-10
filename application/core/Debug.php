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
| Main error handler for the Core.
|
*/
namespace Application\Core;

class Debug extends \System\Core\Debug
{

    // Error Number
    protected $ErrorNo;


/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    public function __construct()
    {
        parent::__construct();
    }

/*
| ---------------------------------------------------------------
| Function: trigger_error()
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
    public function trigger_error($errno, $message = '', $file = '', $line = 0, $backtrace = array())
    {
        // fill attributes
        $this->ErrorNo = $errno;
        $this->ErrorMessage = $message;
        $this->ErrorFile = str_replace(ROOT . DS, '', $file);
        $this->ErrorLine = $line;
        $this->ErrorTrace = $backtrace;

        // Get our level text
        switch($errno)
        {
            case E_USER_ERROR:
                $this->ErrorLevel = 'Error';
                $severity = 2;
                break;

            case E_USER_WARNING:
                $this->ErrorLevel = 'Warning';
                $severity = 1;
                break;
                
            case E_USER_NOTICE:
                $this->ErrorLevel = 'Notice';
                $severity = 1;
                break;
            
            case E_ERROR:
                $this->ErrorLevel = 'Error';
                $severity = 2;
                break;
                
            case E_WARNING:
                $this->ErrorLevel = 'Warning';
                $severity = 1;
                break;
                
            case E_NOTICE:
                $this->ErrorLevel = 'Notice';
                $severity = 1;
                break;
                
            case E_PARSE:
                $this->ErrorLevel = 'Parse Error';
                $severity = 3;
                break;
                
            case E_DEPRECATED:
                $this->ErrorLevel = 'Deprecated';
                $severity = 1;
                break;

            case E_STRICT:
                $this->ErrorLevel = 'Strict';
                $severity = 1;
                break;

            default:
                $this->ErrorLevel = 'Fetal Error: ('.$errno.')';
                $severity = 3;
                break;
        }
        
        // If we are silent, then be silent
        if($severity == 3 || !$this->silence)
        {
            // log error if enabled
            if( $this->log_errors == 1 )
            {
                $this->log_error();
            }
            
            // Only build the error page when its fetal, or a development Env.
            if( $this->Environment == 2 || $severity > 1 )
            {
                // build nice error page
                $this->build_error_page();
            }
        }
    }

/*
| ---------------------------------------------------------------
| Function: log_error()
| ---------------------------------------------------------------
|
| Logs the error message in the error log
|
*/
    protected function log_error()
    {
        // Get our site url
        $url = $this->url_info;
        $DB = load_class('Loader')->database('DB');
        
        // Make neat the error trace ;)
        $trace = array();
        foreach($this->ErrorTrace as $key => $t)
        {
            if($key == 0) continue; 
            unset($t['object']);
            $trace[] = print_r( $t, true );
        }
        
        // Attempt to insert the error in the database
        $data = array(
            'level' => $this->ErrorLevel,
            'string' => $this->ErrorMessage,
            'file' => $this->ErrorFile,
            'line' => $this->ErrorLine,
            'url' => $url['site_url'] ."/". $url['uri'],
            'remote_ip' => $_SERVER['REMOTE_ADDR'],
            'time' => time(),
            'backtrace' => serialize( $trace )
        );
        $DB->insert('pcms_error_logs', $data);
    }
}
// EOF