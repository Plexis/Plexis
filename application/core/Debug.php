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
    
    protected $isAjax;


/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    public function __construct()
    {
        $this->isAjax = load_class('Input')->is_ajax();
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
    public function trigger_error($errno, $message = '', $file = '', $line = 0, $backtrace = NULL)
    {
        // fill attributes
        $this->ErrorLevel = $this->error_string($errno);
        $this->ErrorMessage = $message;
        $this->ErrorFile = $file; //str_replace(ROOT . DS, '', $file);
        $this->ErrorLine = $line;
        $this->ErrorTrace = $backtrace;
        
        // Get our severity
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

        // If we are silent, then be silent
        if($severity == 3 || !$this->silence)
        {
            // Log error based on error log level
            if($this->log_level != 0) $this->log_error();
        
            // Only build the error page when its fetal, or a development Env.
            if( $this->Environment == 2 || $severity > 1 )
            {
                // Output depending on request type
                if($this->isAjax)
                {
                    // Only display ajax errors if they are severe!
                    if($errno != E_STRICT && $errno != E_DEPRECATED)
                    {
                        $string = $this->error_string($errno);
                        echo json_encode( 
                            array(
                                'success' => false,
                                'message' => '['. $string .'] '. $message ." $file [$line]",
                                'data' => '['. $string .'] '. $message,
                                'type' => 'error'
                            )
                        );
                        exit();
                    }
                }
                else
                {
                    $this->build_error_page();
                }
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
    protected function log_error($was_silenced = false)
    {
        // Get our site url
        $url = $this->url_info;
        $DB = load_class('Loader')->database('DB');
        
        // Make neat the error trace ;)
        $trace = array();
        if($this->ErrorTrace != null)
        {
            foreach($this->ErrorTrace as $key => $t)
            {
                if($key == 0) continue; 
                unset($t['object']);
                $trace[] = print_r( $t, true );
            }
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