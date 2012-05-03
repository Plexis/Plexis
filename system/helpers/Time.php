<?php
/*
| ---------------------------------------------------------------
| Function: sec2hms()
| ---------------------------------------------------------------
|
| Converts a timestamp to how many days, hours, mintues left
| Thanks to: http://www.laughing-buddha.net/php/lib/sec2hms/
|
| @Param: (Int) $sec - The timestamp
| @Return (String) The array of data
|
*/
    function sec2hms($sec, $padHours = true) 
    {
        // start with a blank string
        $hms = "";

        // do the hours first: there are 3600 seconds in an hour, so if we divide
        // the total number of seconds by 3600 and throw away the remainder, we're
        // left with the number of hours in those seconds
        $hours = intval(intval($sec) / 3600); 

        // add hours to $hms (with a leading 0 if asked for)
        $hms .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":" : $hours. ":";

        // dividing the total seconds by 60 will give us the number of minutes
        // in total, but we're interested in *minutes past the hour* and to get
        // this, we have to divide by 60 again and then use the remainder
        $minutes = intval(($sec / 60) % 60); 

        // add minutes to $hms (with a leading 0 if needed)
        $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

        // seconds past the minute are found by dividing the total number of seconds
        // by 60 and using the remainder
        $seconds = intval($sec % 60); 

        // add seconds to $hms (with a leading 0 if needed)
        $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

        // done!
        return $hms;
    }
    
/*
| ---------------------------------------------------------------
| Function: format_time()
| ---------------------------------------------------------------
|
| Converts seconds to a human readable time format
|
| @Param: (Int) $sec - The timestamp or seconds
| @Return (String) The array of data
|
*/
    function format_time($seconds)
    {
        // Get our seconds to hours:minutes:seconds
        $time = sec2hms($seconds, false);
        
        // Explode the time
        $time = explode(':', $time);
        
        // Hour corrections
        $set = '';
        if($time[0] > 0)
        {
            // Set days if viable
            if($time[0] > 23)
            {
                $days = floor($time[0] / 24);
                $time[0] = $time[0] - ($days * 24);
                $set .= ($days > 1) ? $days .' Days' : $days .' Day';
                if($time[0] > 0) $set .= ',';
            }
            $set .= ($time[0] > 1) ? $time[0] .' Hours' : $time[0] .' Hour';
        }
        if($time[1] > 0)
        {
            $set .= ($time[0] > 0) ? ', ' : '';
            $set .= ($time[1] > 1) ? $time[1] .' Minutes' : $time[1] .' Minute';
        }
        
        return $set;
    }
    
/*
| ---------------------------------------------------------------
| Function: microtime_float()
| ---------------------------------------------------------------
|
| Returns the absolute microtime in a float
|
*/
    function microtime_float() 
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
    
// EOF