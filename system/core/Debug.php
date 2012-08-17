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
    
    // Logs
    protected static $LogSystem = false;
    protected static $LogDebug = false;
    
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
    
    // Remote debugger variables
    protected static $debugging = true;


/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public static function Init()
    {
        // Add this to the trace
        self::trace('Initializing debug class...', __FILE__, __LINE__);
        
        // Load the config class
        $Config = load_class('Config');
        
        // Fill our error reporting variables
        self::$LogSystem = $Config->get('enable_system_logs', 'Core');
        self::$LogDebug = $Config->get('enable_debug_logs', 'Core');
        self::$Environment = $Config->get('environment', 'Core');
        self::$catchFatalErrors = $Config->get('catch_fatal_errors', 'Core');
        self::$debugging = $Config->get('enable_remote_debugger', 'Core');
        
        // Get our URL info
        self::$urlInfo = load_class('Router')->get_url_info();
        self::$ajaxRequest = load_class('Input')->is_ajax();
        
        // Debugging setup, Make sure we have a trigger, and set the script time limit
        if(self::$debugging) 
            (!isset($_GET['debug'])) ? self::$debugging = false : set_time_limit(600);
        
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
        // Stop debugging if we are doing so
        // Check for a killswitch
        if(self::$debugging)
        {
            // Load our Cache file, and write the new contents
            $Cache = load_class('Cache', 'Library');
            $debug = array(
                'flags' => 2,
                'next_step' => false,
                'file' => null,
                'line' => null,
                'variable' => null,
                'variable_in' => null,
                'variable_mode' => null,
                'output' => null,
            );
            $Cache->save('debugger', $debug, 3);
        }
        
        // Generate the debug log
        $error = error_get_last();
        
        // If we have an error on shutdown, that means we never caught it :O ... its Fatal
        if(is_array($error) && self::$catchFatalErrors == 1 && !self::$outputSent)
        {
            // Trigger
            self::$silence = false;
            return self::trigger_error($error['type'], $error['message'], $error['file'], $error['line']);
        }
        
        // Add this to the trace
        self::trace('Everything complete... Shutting down', __FILE__, __LINE__);
        
        // Generate trace logs
        if( !self::$logged && self::$LogDebug )
        {
            self::write_debuglog();
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
            self::log_error();
        
            // Only build the error page when it's fatal, or a development Env.
            if( (self::$Environment == 2 || $severity > 1) && !self::$outputSent )
            {
                // Generate trace logs
                if( !self::$logged )
                {
                    self::write_debuglog('debug_error_'. time());
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
    public static function write_debuglog($name = null, $uri = true)
    {
        // Are we turning logging off?
        if(is_bool($name)) self::$LogDebug = $name;
        
        // Do we have a custom name?
        $uri = str_replace(array('/', '\\'), '-', trim(self::$urlInfo['uri'], '/'));
        
        // Prevent further errors
        if(!isset($GLOBALS['controller']))
        {
            $GLOBALS['controller'] = 'welcome';
            $GLOBALS['action'] = 'index';
        }
        
        // Determine the name of the debug file name
        if($name == null)
            $name = (empty($uri)) ? 'debug_'. $GLOBALS['controller'] . '-'. $GLOBALS['action'] : 'debug_'. $uri;
        else
            if($uri == true) $name = $name .'_'. $uri;
        
        // Build the xml
        $string = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\r\n<debug>\r\n";
        $string .= "\t<info>\r\n";
        $string .= "\t\t<url>". self::$urlInfo['site_url'] ."/". self::$urlInfo['uri'] ."</url>\r\n";
        $string .= "\t\t<php_version>". PHP_VERSION ."</php_version>\r\n";
        $string .= "\t\t<cms_version>". CMS_VERSION ."</cms_version>\r\n";
        $string .= "\t\t<cms_revision>". CMS_BUILD ."</cms_revision>\r\n";
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
        if( !self::$LogSystem ) return;
        
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








/*
| ---------------------------------------------------------------
| Method: mark()
| ---------------------------------------------------------------
|
*/
    public static function mark($params, $file, $line)
    {
        // Check for a killswitch
        if(self::$debugging == false) return;
        
        // Load our Cache file, and write the new contents
        $Cache = load_class('Cache', 'Library');
        $debug = array(
            'flags' => 0,
            'next_step' => false,
            'file' => str_replace( ROOT . DS, '', $file),
            'line' => $line,
            'variable' => null,
            'variable_in' => null,
            'variable_mode' => null,
            'output' => null,
        );
        $Cache->save('debugger', $debug);
        
        // Keep looping until either the debugging is disabled, or the step_next is enabled
        for($i = 0;; $i++)
        {
            // Load the debugger.cache contents
            if($i > 0) $debug = $Cache->get('debugger');
            
            // Check for a quit debugging flag
            if($debug['flags'] == 1)
            {
                self::$debugging = false;
                break;
            }
            
            // Check for a kill script
            if($debug['flags'] == 2) die('Application was killed by the remote debugger at '. date("F j, Y, g:i a", time()));
            
            // Check for a next step
            if($debug['next_step']) break;
            
            // Check variable changes / requests
            if($debug['variable'] != null)
            {
                // remove the $
                $debug['variable'] = ltrim($debug['variable'], '$');
                
                /* 
                    First we need to explode array keys. So $array[key1][key2] turns into a real array like so 
                    array(array, key1, key2);
                */
                $find = array('"', "'", '[]', ']');
                $v_keys = explode('[', str_replace($find, '', $debug['variable']));
                $num_of_keys = count($v_keys);
                $parts = '';
                
                // Build out array parts as a string
                for($i = 0; $i < $num_of_keys; $i++)
                {
                    $s = $v_keys[$i];
                    $parts .= (is_numeric($s)) ? '['. $s .']' : '[\''. $s .'\']';
                }
                
                // Make sure the variable exists
                $isset = eval('return isset($params'. $parts .');');
                if(!$isset)
                {
                    $debug['output'] = "\${$debug['variable']} = Undefined Variable";
                    $debug['variable'] = null;
                    $debug['variable_in'] = null; 
                }
                elseif($debug['variable_mode'] == 'get')
                {
                    // Get our variable
                    $var = eval('return $params'. $parts .';');
                    
                    // If we have an array or an object, then we use print_r to pring out the array/class all nice and neet
                    if (is_array($var) || is_object($var)) $var = print_r($var, true);

                    // Prepare DB update
                    $find = array('"', "\r", "\n", "\t", "(", ")", "'", "\\", " ");
                    $replace = array("&#34;", "<br/>", "<br/>", "&nbsp;&nbsp;&nbsp;&nbsp;", "&#40;", "&#41;", "&#39;", "&#92;", "&nbsp;");
                    $var = "\${$debug['variable']} = ". str_replace($find, $replace, $var);
                    
                    // Update
                    $debug['variable'] = null;
                    $debug['output'] = $var;
                }
                elseif($debug['variable_mode'] == 'set' && $debug['variable_in'] != null)
                {
                    $var = $debug['variable_in'];
                    switch($var['type'])
                    {
                        case 'int': $val = (int) $var['value']; break;
                        case 'float': $val = (float) $var['value']; break;
                        case 'double': $val = (double) $var['value']; break;
                        case 'bool': $val = (bool) $var['value']; break;
                        default: $val = $var['value']; break;
                    }
                    
                    // set the value
                    eval('$params'. $parts .' = $val;');
                    
                    // Now fetch that variable for a return
                    $var = eval('return $params'. $parts .';');
                    
                    // If we have an array or an object, then we use print_r to pring out the array/class all nice and neet
                    if (is_array($var) || is_object($var)) $var = print_r($var, true);

                    // Prepare DB update
                    $find = array('"', "\r", "\n", "\t", "(", ")", "'", "\\", " ");
                    $replace = array("&#34;", "<br/>", "<br/>", "&nbsp;&nbsp;&nbsp;&nbsp;", "&#40;", "&#41;", "&#39;", "&#92;", "&nbsp;");
                    $var = "\${$debug['variable']} = ". str_replace($find, $replace, $var);
                    
                    // Prepare save
                    $debug['variable'] = null;
                    $debug['variable_in'] = null; 
                    $debug['output'] = $var;
                }
            }

            $Cache->save('debugger', $debug, 2);
            
            // sleep for 0.5 seconds
            usleep(500000);
        }
    }
}

// Initialize the debug class
Debug::Init();

// Register the server to process errors with the this class
set_error_handler( array('Debug', 'trigger_error'), E_ALL | E_STRICT );
register_shutdown_function( array('Debug', 'Shutdown') );
// EOF