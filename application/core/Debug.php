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