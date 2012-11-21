<?php
/* 
| --------------------------------------------------------------
| Plexis Core
| --------------------------------------------------------------
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Benchmark
| ---------------------------------------------------------------
|
| This class is used to benchmark certain parts of the system. You
| can define new start and stop times, and get elapsed times as 
| well
|
*/
namespace Core;

class Benchmark
{
    // Start and stop timers
    protected static $start = array(); 
    protected static $stop = array();
    
    public static function Init()
    {
        self::$start['total_script_exec'] = TIME_START;
    }

/*
| ---------------------------------------------------------------
| Function: Start()
| ---------------------------------------------------------------
|
| Starts a new timer
|
| @Param: (String) $key - Name of this start time
|
*/
    public static function Start($key)
    {
        self::$start[$key] = microtime(true);
    }

/*
| ---------------------------------------------------------------
| Function: Stop()
| ---------------------------------------------------------------
|
| Stops a defined timer
|
| @Param: (String) $key - Name of this timer to be stopped
|
*/
    public static function Stop($key)
    {
        self::$stop[$key] = microtime(true);
    }

/*
| ---------------------------------------------------------------
| Function: ElapsedTime()
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
    public static function ElapsedTime($key, $round = 3, $stop = FALSE)
    {
        if(!isset(self::$start[$key]))
        {
            // show_error('benchmark_key_not_found', array($key), E_WARNING);
            return FALSE;
        }
        else
        {
            if(!isset(self::$stop[$key]) && $stop == TRUE)
            {
                self::$stop[$key] = microtime(true);
            }
            return round( (microtime(true) - self::$start[$key]), $round );
        }
    }
    
/*
| ---------------------------------------------------------------
| Function: MemoryUsage()
| ---------------------------------------------------------------
|
| Returns the amount of memory the system has used to load the page
|
*/
    public static function MemoryUsage() 
    {
        $usage = '';	 
        $mem_usage = memory_get_usage(true); 
        
        if($mem_usage < 1024) 
            $usage =  $mem_usage." Bytes"; 
        elseif($mem_usage < 1048576) 
            $usage = round($mem_usage/1024, 2)." Kilobytes"; 
        else
            $usage = round($mem_usage/1048576, 2)." Megabytes"; 
			
        return $usage;
    }
}

Benchmark::Init();
// EOF 