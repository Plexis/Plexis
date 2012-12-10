<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Benchmark.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    Benchmark
 */
namespace Core;

/**
 * This class is used to benchmark certain parts of the system. You
 * can define new start and stop times, and get elapsed times as well
 *
 * @author      Steven Wilson 
 * @package     Core
 */
class Benchmark
{
    /**
     * An array of benchmark start times ($name => time)
     * @var float[]
     */
    protected static $start = array();
    
    /**
     * An array of benchmark stop times ($name => time)
     * @var float[]
     */
    protected static $stop = array();
    
    /**
     * When this method is called (automatically), the system start time is defined
     *
     * @return void
     */
    public static function Init()
    {
        self::$start['total_script_exec'] = TIME_START;
    }

    /**
     * Starts a new timer
     *
     * When this method is called, a new timer will begin for the provided
     * benchmark name
     *
     * @param string $name The name given for this benchmark timer
     * @return void
     */
    public static function Start($name)
    {
        self::$start[$name] = microtime(true);
    }

    /**
     * Stops a defined timer
     *
     * When this method is called, the provided timer name will be stopped
     *
     * @param string $name The name given for this benchmark timer
     * @return void
     */
    public static function Stop($name)
    {
        self::$stop[$name] = microtime(true);
    }

    /**
     * Returns the final time from start to finish for a benchmark
     *
     * @param string $name The name given for this benchmark timer
     * @param int $round How many numbers after the "." do we show?
     * @param bool $stop Stop the timer as well?
     * @return float|bool The time it took from start to finish. FALSE
     * if no timer was set in the first place.
     */
    public static function ElapsedTime($name, $round = 3, $stop = FALSE)
    {
        if(!isset(self::$start[$name]))
        {
            // show_error('benchmark_key_not_found', array($name), E_WARNING);
            return FALSE;
        }
        else
        {
            if(!isset(self::$stop[$name]) && $stop == TRUE)
            {
                self::$stop[$name] = microtime(true);
            }
            return round( (microtime(true) - self::$start[$name]), $round );
        }
    }
    
    /**
     * Returns the amount of memory the system has used to load the page
     *
     * @return float
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