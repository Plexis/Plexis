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
| Class: Soap()
| ---------------------------------------------------------------
|
| A telnet connection wrapper for Plexis CMS
|
*/
namespace Application\Library;

class Telnet
{
    protected $handle;
    protected $Debug;
    protected $console_return;
    protected $debug = array();

/*
| ---------------------------------------------------------------
| Method: connect()
| ---------------------------------------------------------------
|
| This method is used to initiate the Telnet Connection
|
| @Param: (String) $host - The servers IP address
| @Param: (Int) $port - The Telnet port
| @Param: (String) $user - The login username
| @Param: (String) $pass - The login password
| @Return (Bool): True on success, FALSE otherwise
|
*/ 
    public function connect($host, $port, $user, $pass)
    {
        // Disable error reporting
        $Debug = load_class('Debug');
        $Debug->silent_mode(true);
        
        // Open the handle
        $this->handle = @fsockopen($host, $port, $errno, $errstr, 3);
        
        // Re-enable error reporting
        $Debug->silent_mode(false);
        
        // Check if we connected successfully
        if($this->handle)
        {
            // get the Login prompt
            $auth_prompt = $this->get_response();

            // Uppercase Username
            $user = strtoupper($user);

            // Write username and password to command window
            $send1 = $this->send($user);
            $send2 = $this->send($pass);

            // If our writing is successful
            if($send1 == TRUE && $send2 == TRUE)
            {
                // Get the motd OR auth error, 1 of the 2
                $response = $this->get_response();
                if(strpos($response, "failed") !== FALSE)
                {
                    $this->console_return = $this->debug[] = 'Authorization Failed.';
                    $this->write_log();
                    $this->disconnect();
                    return FALSE;
                }
                return TRUE;
            }
            else
            {
                $this->debug[] = 'Writing to console failed!';
            }
        }
        else
        {
            $this->console_return = $this->debug[] = 'Connection to '.$host.':'.$port.' Failed!';
        }
        
        // If we are here, we had errors :(
        $this->write_log();
        return FALSE;
    }
    
/*
| ---------------------------------------------------------------
| Method: disconnect()
| ---------------------------------------------------------------
|
| This method is used to close the Telnet Connection
|
| @Return (None)
|
*/ 
    public function disconnect() 
    {
        if($this->handle) 
        {
            if($this->handle) fclose($this->handle);
            $this->handle = NULL;
        }
        return TRUE;
    }

/*
| ---------------------------------------------------------------
| Method: send()
| ---------------------------------------------------------------
|
| This method is used to send a command to the Telnet Connection
|
| @Param: (String) $command - The command string
| @Return (Bool): TRUE on success, or FALSE
|
*/ 
    public function send($command) 
    {
        if($this->handle) 
        {
            $send = fputs($this->handle, $command . PHP_EOL);
            $this->sleep();
            $this->console_return = fgets($this->handle, 2048);
            return $send;
        }
        return FALSE;
    }

/*
| ---------------------------------------------------------------
| Method: get_response()
| ---------------------------------------------------------------
|
| This method is used to get the last response of the Connection
|
| @Return (String): Repsonse String
|
*/ 	
    public function get_response()
    {
        // No handle
        return $this->console_return;
    }

/*
| ---------------------------------------------------------------
| Method: sleep()
| ---------------------------------------------------------------
|
| Delays the next command to prevent errors
|
*/ 	
    public function sleep() 
    {
        usleep(300);
        return;
    }

/*
| ---------------------------------------------------------------
| Method: write_log()
| ---------------------------------------------------------------
|
| Logs any and all errors
|
*/     
    private function write_log()
    {
        $date = date('Y-m-d H:i:s');
        $outmsg = array();
        $outmsg[] = "/ ******************************************************************";
        $outmsg[] = "Ra Debugging Log for date: ". $date . PHP_EOL;
        
        foreach($this->debug as $line)
        {
            $outmsg[] = $line;
        }
        
        $outmsg[] = "****************************************************************** /" . PHP_EOL;
        $file = fopen( SYSTEM_PATH . DS . 'logs' . DS . 'ra_debug.log', 'a' );
        foreach($outmsg as $msg)
        {
            fwrite($file, " ". $msg . PHP_EOL);
        }
        fclose($file);
    }
}
// EOF