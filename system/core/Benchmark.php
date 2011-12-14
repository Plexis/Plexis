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
*/
namespace System\Core;

class Benchmark
{
    // Start and stop timers
    protected $start = array(); 
    protected $stop = array();

/*
| ---------------------------------------------------------------
| Function: start()
| ---------------------------------------------------------------
|
| Starts a new timer
|
| @Param: (String) $key - Name of this start time
|
*/
    public function start($key)
    {
        $this->start[$key] = microtime(true);
    }

/*
| ---------------------------------------------------------------
| Function: stop()
| ---------------------------------------------------------------
|
| Stops a defined timer
|
| @Param: (String) $key - Name of this timer to be stopped
|
*/
    public function stop($key)
    {
        $this->stop[$key] = microtime(true);
    }

/*
| ---------------------------------------------------------------
| Function: elapsed_time()
| ---------------------------------------------------------------
|
| Returns the final time from start to finish
|
| @Param: (String) $key - Name of this timer to be shown
| @Param: (Int) $round - How many numbers after the "." do we show?
| @Param: (Bool) $stop - Stop the timer as well?
| @Return: (Float) - The time it took from start to finish. FALSE
|	if no timer was set in the first place.
|
*/
    public function elapsed_time($key, $round = 3, $stop = FALSE)
    {
        if(!isset($this->start[$key]))
        {
            show_error('benchmark_key_not_found', array($key), E_WARNING);
            return FALSE;
        }
        else
        {
            if(!isset($this->stop[$key]) && $stop == TRUE)
            {
                $this->stop[$key] = microtime(true);
            }
            return round( (microtime(true) - $this->start[$key]), $round );
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: memory_usage()
| ---------------------------------------------------------------
|
| Returns the amount of memory the system has used to load the page
|
*/
    public function memory_usage() 
    {
        $usage = '';	 
        $mem_usage = memory_get_usage(true); 
        
        if($mem_usage < 1024) 
        {
            $usage =  $mem_usage." Bytes"; 
        }
        elseif($mem_usage < 1048576) 
        {
            $usage = round($mem_usage/1024, 2)." Kilobytes"; 
        }
        else
        { 
            $usage = round($mem_usage/1048576, 2)." Megabytes"; 
        }	
        return $usage;
    }
}
// EOF 