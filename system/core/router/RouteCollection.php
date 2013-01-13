<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Core/Router/RouteCollection.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @contains    RouteCollection
 */
namespace Core\Router;

/**
 * A container class to hold route information for the router.
 *
 * @author      Steven Wilson 
 * @package     Core
 * @subpackage  Router
 */
class RouteCollection
{
    /**
     * A Multi-Demensional array of route information
     * @var Route[]
     */
    protected $routes = array();
    
    /**
     * Adds a route for a module, and URI segements
     *
     * @param Route $route The route object to add
     *
     * @return void
     */
    public function addRoute( Route $route ) 
    {
        // Add route
        $this->routes[$route->getMatch()] = $route;
    }
    
    /**
     * Merges another RouteCollections route with this collection.
     *
     * @param RouteCollection $Routes The route collection to merge with
     *
     * @return void
     */
    public function merge( RouteCollection $Routes )
    {
        $r = $Routes->getRoutes();
        foreach($r as $match => $r)
            $this->routes[$match] = $r;
    }
    
    /**
     * Removes the specified route from the list of routes.
     *
     * @param string $route The regular expression to remove
     *
     * @return void
     */
    public function removeRoute($route) 
    {
        unset($this->routes[$route]);
    }
    
    /**
     * Returns whether there is a match to the URI route supplied.
     *
     * @param string $route The route string
     * @param string[] $match [Reference Variable] Returns the controller, 
     *   action, and params if the route is a match.
     *
     * @return bool Returns whether or not a match was found
     */
    public function hasRoute($route, &$match = array())
    {
        // Match the route
        foreach($this->routes as $r)
        {
            if($r->match($route, $match))
                return true;
        }
        
        return false;
    }
    
    /**
     * Returns a list of all defined routes in this stack.
     *
     * @return Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}