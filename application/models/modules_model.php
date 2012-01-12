<?php

class Modules_Model extends Application\Core\Model 
{

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    function __construct()
    {
        parent::__construct();
    }
    
/*
| ---------------------------------------------------------------
| Method: install()
| ---------------------------------------------------------------
|
| Adds a new module to the database and installs it
|
| @Param: (String) $name - The name of the module
| @Param: (String) $uri - The URI link to access the module
| @Param: (String) $method - The method that laods with this $uri
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function install($name, $uri, $method)
    {
        // Load the module controller
        $file = APP_PATH . DS . 'modules' . DS . $name . DS .'controller.php';
        if(!file_exists($file))
        {
            return FALSE;
        }
        require $file;
        
        // Init the module into a variable
        $class = ucfirst($name);
        $module = new $class(FALSE);
        
        // Run the module installer
        $result = $module->_install();
        if($result == FALSE) return FALSE;
        
        // Make sure we have a fixed URI
        $uri = rtrim($uri, '/');
        $uri = ltrim($uri, '/');
        if(strpos($uri, '/') === FALSE)
        {
            $uri = $uri .'/index';
        }
        
        // Build out insert data
        $data['name'] = $name;
        $data['uri'] = $uri;
        $data['method'] = $method;
        $data['has_admin'] = $module->has_admin();
        
        // Insert our post
        return $this->DB->insert('pcms_modules', $data);
    }
    
/*
| ---------------------------------------------------------------
| Method: uninstall()
| ---------------------------------------------------------------
|
| Uninstalls a modulde from site use
|
| @Param: (String) $name - The name of the module
| @Return (Bool) TRUE if successful, FALSE otherwise
|
*/
    public function uninstall($name)
    {
        // Load the module controller
        $file = APP_PATH . DS . 'modules' . DS . $name . DS .'controller.php';
        if(!file_exists($file))
        {
            return FALSE;
        }
        require $file;
        
        // Init the module into a variable
        $class = ucfirst($name);
        $module = new $class(FALSE);
        
        // Run the module installer
        $result = $module->_uninstall();
        if($result == FALSE) return FALSE;
        
        // Delete our post and return the result
        $name = strtolower($name);
        return $this->DB->delete('pcms_modules', "`name`='$name'");
    }
}
// EOF