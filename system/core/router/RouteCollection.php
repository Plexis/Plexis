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
     * @var array[]
     */
    protected $routes = array();
    
    /**
     * Adds a route for a module, and URI segements
     *
     * @param \Core\Module $M The module we are adding a route for
     * @param string $moduleUri The module uri segement
     * @param string $actionUri The action uri segement
     *
     * @return void
     */
    public function addRoute( \Core\Module $M, $moduleUri, $actionUri ) 
    {
        // Check for a wildcard
        if(isset($this->routes[ $moduleUri .'_*' ]))
            continue;
        
        // Add Route
        $this->routes[ $moduleUri .'_'. $actionUri ] = array(
            'module' => $M->getName(),
            'controller' => $M->getControllerName(),
            'action' => $M->getActionName(),
            'core' => $M->isCoreModule()
        );
    }
    
    /**
     * Adds a route for a module, based off a list of parameters
     *
     * @param string $moduleName The name of the module we are adding a route for
     * @param string $uriString The 2 part URI string we are routing for. Must be formatted
     *   correctly (module/action).
     * @param string $controller The module controller that will handle the route request.
     * @param string $action The module controller method that will handle the route request.
     * @param bool $coreModule Is this a core module? This bool affects the path to the module's root
     *   folder.
     *
     * @throws \InvalidArgumentException Thrown if the $uriString is invalid.
     *
     * @return void
     */
    public function addRouteParams($moduleName, $uriString, $controller = null, $action = null, $coreModule = false) 
    {
        // make sure we have a valid URI string
        $uri = trim( $uriString, '/');
        if(substr_count($uri, '/') != 1)
            throw new \InvalidArgumentException("Uri string must contain 2 parts.. A module request, and an Action request");
        
        // Get both uri parts
        $uriParts = explode("/", $uri);
        
        // Check for a wildcard
        if(isset($this->routes[ $uriParts[0] .'_*' ]))
            continue;
        
        // Add Route
        $this->routes[ str_replace('/', '_', $uriString) ] = array(
            'module' => $moduleName,
            'controller' => (empty($controller)) ? ucfirst($moduleName) : ucfirst($controller),
            'action' => (empty($action)) ? $uriParts[1] : $action,
            'core' => $coreModule
        );
    }
    
    /**
     * Takes an array of routes to add to the database.
     *
     * @param \Core\Module $M The module we are adding a routes for
     * @param array[] $routes A Two Dimensional Array containing information
     *   about the route. Array keys are ('module_uri', 'action_uri', 'controller',
     *   'method', 'core');
     *
     * @return void
     */
    public function addRoutesArray( \Core\Module $M, array $routes ) 
    {
        // Define some vars
        $name = $M->getName();
        $isCore = $M->isCoreModule();
        
        foreach($routes as $route)
        {
            // Check for a wildcard
            if(isset($this->routes[ $route['module_uri'] .'_*' ]))
                continue;
            
            $key = $route['module_uri'] .'_'. $route['action_uri'];
            $this->routes[$key] = array(
                'module' => $name,
                'controller' => (isset($route['controller'])) ? $route['controller'] : null,
                'action' => (isset($route['method'])) ? $route['method'] : null,
                'core' => $isCore
            );
        }
    }
    
    /**
     * Takes a module object, and Adds its routes to the list.
     *
     * @param \Core\Module $M The module we are adding routes for
     *
     * @return void
     */
    public function addModuleRoutes( \Core\Module $M  ) 
    {
        // Define some vars
        $name = $M->getName();
        $isCore = $M->isCoreModule();
        $Xml = $M->getModuleXml();
        
        foreach($Xml->routes->children() as $route)
        {
            // Make sure all attributes are assigned
            if(!isset($route['module']) || !isset($route['action']))
                continue;
                
            // Check for a wildcard
            if(isset($this->routes[ $route['module'] .'_*' ]))
                continue;
            
            // Define array key, and fix action
            $key = $route['module'] .'_'. $route['action'];
            
            // Add Route
            $this->routes[$key] = array(
                'module' => $name,
                'controller' => (isset($route->controller)) ? ucfirst($route->controller) : $name,
                'action' => (isset($route->method)) ? $route->method : $route['action'],
                'core' => $isCore
            );
        }
    }
    
    /**
     * Removes the specified route from the list of routes.
     *
     * @param string $moduleUri The module uri segement
     * @param string $actionUri The action uri segement
     *
     * @return void
     */
    public function removeRoute($moduleUri, $actionUri) 
    {
        unset($this->routes[$moduleUri .'_'. $actionUri]);
    }
    
    /**
     * Removes all routes from the list from the specified module name.
     *
     * @param string $moduleName
     *
     * @return void
     */
    public function removeRoutesByModule($moduleName) 
    {
        $count = count($this->routes);
        foreach($this->routes as $key => $route)
        {
            if($route['module'] == $moduleName)
                unset($this->routes[$key]);
        }
    }
    
    /**
     * Specified whether or not a route has been defined.
     *
     * @param string $moduleUri The module uri segement
     * @param string $actionUri The action uri segement
     *
     * @return bool
     */
    public function hasRoute($moduleUri, $actionUri)
    {
        return (isset($this->routes[$moduleUri .'_*']) || isset($this->routes[$moduleUri .'_'. $actionUri]));
    }
    
    /**
     * Returns a list of all defined routes in this stack.
     *
     *
     * @return array[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }
    
    /**
     * Formats the current stack list into an insert sql statement
     *
     * @return string
     */
    public function toSql()
    {
        $sql = "INSERT INTO `pcms_routes` VALUES ";
        foreach($this->routes as $key => $v)
        {
            $p = explode('_', $key);
            $core = ($v['core']) ? 1 : 0;
            $sql .= "('{$p[0]}', '{$p[1]}', '{$v['module']}', '{$v['controller']}', '{$v['action']}', '{$core}'),";
        }
        
        return rtrim($sql, ",");
    }
}