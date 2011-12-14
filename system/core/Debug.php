<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
| ---------------------------------------------------------------
| Class: Debug
| ---------------------------------------------------------------
|
| Main error handler for the Core.
|
*/
namespace System\Core;

class Debug
{
    // The instance of this class 
    private static $instance;

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

    // Our current language
    protected $lang;

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
        // Language setup
        $this->lang = strtolower( config('core_language', 'Core') );
        
        // fill attributes
        $this->ErrorMessage = $message;
        $this->ErrorFile = $file; //str_replace(ROOT . DS, '', $file);
        $this->ErrorLine = $line;
        $this->ErrorTrace = $backtrace;
        $this->Environment = config('environment', 'Core');

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
                
            case E_STRICT:
                $this->ErrorLevel = 'Strict';
                $severity = 1;
                break;

            default:
                $this->ErrorLevel = 'Error Code: '.$errno;
                $severity = 2;
                break;
        }
        
        // log error if enabled
        if( config('log_errors', 'Core') == 1 )
        {
            $this->log_error();
        }
        
        // Only build the error page when its fetal, or a development Env.
        if( $this->Environment == 2 || $severity == 2 )
        {
            // build nice error page
            $this->build_error_page();
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
        
        // Language setup
        $lang = strtolower( config('core_language', 'Core') );
        
        // See if there is a custom page in the app folder
        if(file_exists( APP_PATH . DS . 'pages' . DS . $this->lang . DS . $lang . DS . $type .'.php' ))
        {
            include(APP_PATH . DS . 'pages' . DS . $this->lang . DS . $lang . DS . $type .'.php');
            die();
        }
        else
        {
            include(SYSTEM_PATH . DS . 'pages' . DS . $this->lang . DS . $lang . DS . $type .'.php');
            die();
        }
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
        
        // Capture the template using Output Buffering, file depends on Environment
        ob_start();
            if($this->Environment == 1)
            {
                include(SYSTEM_PATH . DS . 'pages' . DS . $this->lang . DS . 'error.php');
            }
            else
            {
                include(SYSTEM_PATH . DS . 'pages' . DS . $this->lang . DS . 'debug_error.php');
            }
            $page = ob_get_contents();
        @ob_end_clean();
        
        // If we are debugging, build the debug block
        if($this->Environment == 2)
        {
            // Create the regex, and search for it
            $regex = "{DEBUG}(.*){/DEBUG}";
            while(preg_match("~". $regex ."~iUs", $page, $match))
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
                        if($i == 2) { break; }
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
        /* eval('?>'.$page.'<?'); */
        echo $page;
        die();
    }

/*
| ---------------------------------------------------------------
| Function: log_error()
| ---------------------------------------------------------------
|
| Logs the error message in the error log
|
*/
    function log_error()
    {
        // Setup the url and remove html breaks in the log file
        $url = (isset($_GET['url'])) ? $_GET['url'] : '';
        
        // Create our log message
        $err_message =  "| Logging started at: ". date('Y-m-d H:i:s') ."\n";
        $err_message .= "| Error Level: ".$this->ErrorLevel ."\n";
        $err_message .= "| Message: ".$this->ErrorMessage ."\n"; 
        $err_message .= "| Reporting File: ".$this->ErrorFile."\n";
        $err_message .= "| Error Line: ".$this->ErrorLine."\n";
        $err_message .= "| URL When Error Occured: ".SITE_URL . "/". $url ."\n\n";
        $err_message .= "--------------------------------------------------------------------\n\n";

        // Write in the log file, the very long message we made
        $log = @fopen(SYSTEM_PATH . DS . 'logs' . DS . 'error.log', 'a');
        @fwrite($log, $err_message);
        @fclose($log);
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

/*
| ---------------------------------------------------------------
| Function: php_error_handler()
| ---------------------------------------------------------------
|
| Php uses this error handle instead of the default one because
| php calls this method statically
|
*/
    public static function php_error_handler($errno, $errstr, $errfile, $errline)
    {
        if(!$errno) 
        {
            // This error code is not included in error_reporting
            return;
        }
        
        // Get singleton
        $self = self::singleton();	
        
        // Trigger
        $self->trigger_error($errno, $errstr, $errfile, $errline, debug_backtrace());

        // Don't execute PHP internal error handler
        return true;
    }

/*
| ---------------------------------------------------------------
| Method: singlton()
| ---------------------------------------------------------------
|
| Allows access to the none static methods in the class
|
*/ 

    public static function singleton() 
    {
        if(!isset(self::$instance))
        {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
// EOF