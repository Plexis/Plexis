<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2012, Steven Wilson
| License:      GNU GPL v3
|
| ---------------------------------------------------------------
| Class: Event
| ---------------------------------------------------------------
|
| This class is used to register certain functions / class methods 
| to be notified / called when an event name is fired.
|
*/
namespace System\Core;

class Events
{
	// Static array of events, and handlers
	protected static $events = array(); 

/*
| ---------------------------------------------------------------
| Method: trigger()
| ---------------------------------------------------------------
|
| This method triggers an event
|
| @Param: (String) $event - Name of the event to be fired
| @Param: (Array) $params - An array of params to be passed
|
*/
	public function trigger($event, $params = array())
	{	
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

/*
| ---------------------------------------------------------------
| Method: register()
| ---------------------------------------------------------------
|
| Registers a new class->method / function to be called when an
| event is fired.
|
| @Param: (String) $event - Name of the event that will be called
| @Param: (Array | String) $callback - An array of ($Obj, method).. or
|	function name to be called as a callback.
|
*/
	public function register($event, $callback)
	{
		self::$events[$event][] = $callback;
	}
}
// EOF 