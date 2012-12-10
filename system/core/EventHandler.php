<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/EventHandler.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    EventHandler
 * @contains    InvalidCallableException
 */
namespace Core;

/**
 * Responsible for initializing the controller, and
 * calling on the action method.
 *
 * @author      Steven Wilson 
 * @package     Core
 */
class EventHandler
{
	/**
     * An array of eventname => callbacks[]
     * @var callable[]
     */
	protected static $events = array();
    
    /**
     * Triggers an event.
     *
     * @param string $event The event name
     * @param mixed[] $params Array of params to be passed to all callbacks
     *    registered for this event.
     * @return void
     */
	public static function Trigger($event, $params = array())
	{
        // Get calling
        //$backtrace = debug_backtrace();
        
        // Add trace for debugging
        // \Debug::trace("Event \"{$event}\" triggered from: {$backtrace[0]['file']} on line {$backtrace[0]['line']}", __FILE__, __LINE__);
        
        // Free up memory
        //unset($backtrace);
        
        // Check that event exists
		if(array_key_exists($event, self::$events))
		{
			foreach(self::$events[$event] as $callback)
			{
				// If the callback is an array, then we call a class/method
				if(is_array($callback))
				{
					list($c, $a) = $callback;
					
					// Try and proccess this manually as call_user_func_array is 2x slower then this!
					switch(count($params)) 
					{
						case 0: $c->{$a}(); break;
						case 1: $c->{$a}($params[0]); break;
						case 2: $c->{$a}($params[0], $params[1]); break;
						case 3: $c->{$a}($params[0], $params[1], $params[2]); break;
						case 4: $c->{$a}($params[0], $params[1], $params[2], $params[3]); break;
						case 5: $c->{$a}($params[0], $params[1], $params[2], $params[3], $params[4]); break;
						default: call_user_func_array(array($c, $a), $params);  break;
					}
				}
				else
				{
					// Try and proccess this manually as call_user_func_array is 2x slower then this!
					switch(count($params)) 
					{
						case 0: $callback(); break;
						case 1: $callback($params[0]); break;
						case 2: $callback($params[0], $params[1]); break;
						case 3: $callback($params[0], $params[1], $params[2]); break;
						case 4: $callback($params[0], $params[1], $params[2], $params[3]); break;
						case 5: $callback($params[0], $params[1], $params[2], $params[3], $params[4]); break;
						default: call_user_func_array($callback, $params);  break;
					}
				}
			}
			
			return true;
		}
		return false;
	}
    
    /**
     * Registers a new class->method or function to be called when an
     * event is fired.
     *
     * @param string $event Name of the event to register for
     * @param callable $callback the callback to proccess the event when fired
     * @return void
     * @throws InvalidCallableException if the callback is not a callable
     */
	public static function Register($event, $callback)
	{
        if(!is_callable($callback))
            throw new InvalidCallableException('Invalid Callable recieved');
            
		self::$events[$event][] = $callback;
	}
}

// Class Exceptions

/**
 * Invalid Callable Exception, Thrown when a callback is invalid
 * @package Core
 * @subpackage Exceptions
 */
class InvalidCallableException extends \Exception {}
// EOF 