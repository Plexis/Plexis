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
namespace Core;

class Debug
{

    // Error Number
    protected $ErrorNo;
    
    // Error message,
    protected $ErrorMessage;

    // Error file.
    protected $ErrorFile;

    // Error line.
    protected $ErrorLine;

    // Error Level Text.
    protected $ErrorLevel;

    // Error Backtrace.
    protected $ErrorTrace;
    
    // Our log error level.
    protected $log_level;
    
    // Our sites error level.
    protected $Environment;

    // Silent Mode
    protected $silence = FALSE;
    
    // Array of debug and system messages to be logged
    protected $debug_logs = array();
    protected $system_logs = array();
    
    // Our URL info
    protected $url_info;
    
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
        $this->log_level = $this->config->get('log_level', 'Core');
        $this->Environment = $this->config->get('environment', 'Core');
        
        // Get our URL info
        $this->url_info = load_class('Router')->get_url_info();
        $this->isAjax = load_class('Input')->is_ajax();
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
        
            // Only build the error page when it's fatal, or a development Env.
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
        $DB = load_class('Loader')->database('DB', false, true);
        
        // Only log if database is connectable
        if(is_object($DB))
        {
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
        else
        {
            // Get our site url
            $url = $this->url_info;
            
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
| Function: show_error()
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
        $site_url = $this->url_info['site_url'];
        
        // Include error page
        include(SYSTEM_PATH . DS . 'errors' . DS . 'error_'. $type .'.php');
        
        // Kill the script
        die();
    }
    
/*
| ---------------------------------------------------------------
| Function: log()
| ---------------------------------------------------------------
|
| Logs a message for file writting
|
*/
    public function log($type, $message)
    {
        // Determine if the user wants this logged
        switch( $this->log_level )
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
        
        // Next get our filename
        switch( strtolower($type) )
        {
            case "debug":
                $this->debug_logs[] = " - ". $message; 
                break;
            case "info":
            case "error":
                $this->system_logs[] = "[".date('Y-m-d H:i:s') ."] ". ucfirst($type) .": ". $message ."\n"; 
                break;
            default:
                return;
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: write_logs()
| ---------------------------------------------------------------
|
| Writes the debug / system logs
|
*/
    public function write_logs()
    {
        // Write debugging logs first
        if(!empty($this->debug_logs))
        {
            $message = "Logging started at: ". date('Y-m-d H:i:s') . PHP_EOL;
            $message .= implode( PHP_EOL, $this->debug_logs );
            
            // Write in the log file, the very long message we made
            $log = @fopen(SYSTEM_PATH . DS . 'logs' . DS . 'debug.log', 'w');
            if($log)
            {
                @fwrite($log, $message);
                @fclose($log);
            }
        }
        
        // Write system logs first
        if(!empty($this->system_logs))
        {
            $message = implode( PHP_EOL, $this->system_logs );
            
            // Write in the log file, the very long message we made
            $log = @fopen(SYSTEM_PATH . DS . 'logs' . DS . 'system_logs.log', 'a');
            if($log)
            {
                @fwrite($log, $message);
                @fclose($log);
            }
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: silent_mode()
| ---------------------------------------------------------------
|
| Enable / disable error reporting (except for fatal errors)
|
*/
    public function silent_mode($silent = TRUE)
    {
        $this->silence = $silent;
        return TRUE;
    }
    
/*
| ---------------------------------------------------------------
| Function: build_error_page()
| ---------------------------------------------------------------
|
| Builds the error page and displays it
|
*/
    protected function build_error_page()
    {
        // Clear out all the old junk so we don't get 2 pages all fused together
        if(ob_get_level() != 0) ob_end_clean();
        
        // Get our site url
        $site_url = $this->url_info['site_url'];
        
        // Capture the orig_settingslate using Output Buffering, file depends on Environment
        ob_start();
            $file = SYSTEM_PATH . DS . 'errors' . DS . 'general_error.php';
            if(file_exists($file))
            {
                include($file);
            }
            $page = ob_get_contents();
        @ob_end_clean();
        
        // If we are debugging, build the debug block
        if($this->Environment == 2)
        {
            // Create the regex, and search for it
            $regex = "{DEBUG}(.*){/DEBUG}";
            if(preg_match("~". $regex ."~iUs", $page, $match))
            {
                $blocks = ''; // Each block of backtrace will be added here
                
                // We dont need the first trace.
                unset($this->ErrorTrace[0]);
                $i = 1;
                
                // Make sure we have at least 1 backtrace!
                if(count($this->ErrorTrace) > 0)
                {
                    // Loop through each level and add it to the $blocks var.
                    foreach($this->ErrorTrace as $key => $value)
                    {
                        $block = $match[1];
                        $block = str_replace('{#}', $key++, $block);
                        
                        // Loop though each variable in the Trace level
                        foreach($value as $k => $v)
                        {
                            // Upper case the key
                            $k = strtoupper($k);
                            
                            // If $v is an object, then go to next loop
                            if(is_object($v)) continue;
                            
                            // If $v is an array, we need to dump it
                            if(is_array($v))
                            {
                                $v = "<pre>" . $this->var_dump($v, $k) . "</pre>";
                            }
                            $block = str_replace("{".$k."}", $v, $block);
                        }
                        
                        // Add to blocks
                        $blocks .= $block;
                        
                        // We only want to do this no more then 3 times
                        if($i == 2) break;
                        $i++;
                    }
                }
                
                // Finally replace the whole thing with $blocks
                $page = str_replace($match[0], $blocks, $page);
            }
        }
        
        // alittle parsing
        $page = str_replace("{ERROR_LEVEL}", $this->ErrorLevel, $page);
        $page = str_replace("{MESSAGE}", $this->ErrorMessage, $page);
        $page = str_replace("{FILE}", $this->ErrorFile, $page);
        $page = str_replace("{LINE}", $this->ErrorLine, $page);
        $page = preg_replace('~{(.*)}~', '', $page);
        
        // Spit the page out
        echo $page;
        die();
    }
    
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

/*
| ---------------------------------------------------------------
| Function: var_dump()
| ---------------------------------------------------------------
|
| Creates a nice looking dump of an array. Thanks to Highstrike
| http://www.php.net/manual/en/function.var-dump.php#80288
|
*/

    protected function var_dump($var, $var_name = NULL, $indent = NULL)
    {	
        // Init our empty html return
        $html = '';
        
        // Create our indent style
        $tab_line = "<span style='color:#eeeeee;'>|</span> &nbsp;&nbsp;&nbsp;&nbsp ";


        // Grab our variable type and get our text color
        $type = ucfirst(gettype($var));
        switch($type)
        {
            case "Array":
                // Count our number of keys in the array
                $count = count($var);
                $html .= "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:#a2a2a2'>$type ($count)</span><br />$indent(<br />";
                $keys = array_keys($var);
                
                // Foreach array key, we need to get the value.
                foreach($keys as $name)
                {
                    $value = $var[$name];
                    $html .= $this->var_dump($value, "['$name']", $indent.$tab_line);
                }
                $html .= "$indent)<br />";
                break;
                
            case "String":
                $type_color = "<span style='color:green'>";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($var).")</span> $type_color\"$var\"</span><br />";
                break;
                
            case "Integer":
                $type_color = "<span style='color:red'>";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($var).")</span> $type_color$var</span><br />";
                break;
                
            case "Double":
                $type_color = "<span style='color:red'>"; 
                $type = "Float";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($var).")</span> $type_color$var</span><br />";
                break;
                
            case "Boolean":
                $type_color = "<span style='color:blue'>";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($var).")</span> $type_color".($var == 1 ? "TRUE":"FALSE")."</span><br />";
                break;
                
            case "NULL":
                $type_color = "<span style='color:black'>";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($var).")</span> ".$type_color."NULL</span><br />";
                break;
                
            case "Object":
                $type_color = "<span style='color:black'>";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type</span><br />";
                break;
                
            case "Resource":
                $type_color = "<span style='color:black'>";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type</span> ".$type_color."Resource</span><br />";
                break;
                
            default:
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".@strlen($var).")</span> $var<br />";
                break;
        }

        // Return our variable dump :D
        return $html;
    }
}
// EOF