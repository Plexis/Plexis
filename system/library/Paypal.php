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
| Class: Paypal
| ---------------------------------------------------------------
|
| A Class used to validate paypal payments, and submit simple
| forms to paypal for processing.
|
*/
namespace System\Library;

class Paypal 
{
    // Set our local variables
    var $isTest = FALSE;
    var $logFile;

    
/*
| ---------------------------------------------------------------
| Function: test_mode
| ---------------------------------------------------------------
|
| Sets the mode.
|
| @Param: $value - Set to true for test mode
|
*/

    function test_mode($value)
    {
        $this->isTest = $value;
    }

/*
| ---------------------------------------------------------------
| Function: get_address
| ---------------------------------------------------------------
|
| Used to get the URL based off the test_mode
|
*/

    function get_address()
    {
        if($this->isTest == TRUE)
        {
            return 'www.sandbox.paypal.com';
        } 
        else 
        {
            return 'www.paypal.com';
        }
    }
 
/*
| ---------------------------------------------------------------
| Function: set_log_file
| ---------------------------------------------------------------
|
| Sets the path to the log file
|
| @Param: $logfile - The path.
|
*/

    function set_log_file($logFile)
    {
        $this->logFile = $logFile;
    }

/*
| ---------------------------------------------------------------
| Function: write_log
| ---------------------------------------------------------------
|
| Writes to the log file
|
| @Param: $msg - The message
|
*/

    private function write_log($msg)
    {
        // Add a timestamp to our message
        $outmsg = date('Y-m-d H:i:s')." : ".$msg."<br />\n";
        
        // Dont log anything if we dont have a log file
        if($this->logFile != NULL)
        {
            $file = fopen($this->logFile,'a');
            fwrite($file,$outmsg);
            fclose($file);
        }
    }

/*
| ---------------------------------------------------------------
| Function: check_payment
| ---------------------------------------------------------------
|
| Used to check payment status
|
*/
    function check_payment()
    {
        $req = 'cmd=_notify-validate';
        foreach($_POST as $key => $value) 
        {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }		
        $url = $this->get_address();
        
        // Headers to post back to paypal
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
        $fp = fsockopen('ssl://'.$url, 443, $errno, $errstr, 30);
        
        // $fp = fsockopen ($url, 80, $errno, $errstr, 30);

        // If we cant open socket, we have an error
        if (!$fp) 
        {
            return FALSE;
        } 
        else 
        {
            fputs($fp, $header . $req);
            
            // Common mistake by people is ending the loop on the first run
            // around, which only gives the header line. There for we are going
            // to wait until the loop is ended before returning anything.
            $loop = FALSE; # Start by saying the loop is false on a verified return
            while(!feof($fp)) 
            {
                $res = fgets($fp, 1024);
                if(strcmp($res, "VERIFIED") == 0) # If line result length matches VERIFIED
                {				
                    $loop = TRUE; # Define the loop contained VERIFIED
                } 
            }
            if($loop == TRUE)
            {
                return TRUE;
            }
            else 
            {
                if($this->logFile != NULL) # If user defined a log file
                {
                    $err = array();
                    $err[] = '--- Start Transaction ---';
                    foreach($_POST as $var)
                    {
                        $err[] = $var;
                    }
                    $err[] = '--- End Transaction ---';
                    $err[] = '';
                    foreach($err as $logerror)
                    {
                        $this->write_log($logerror); # Log for error checking
                    }
                }
                return FALSE;
            }
            fclose ($fp);
        }
        return false;
    }
}
// EOF