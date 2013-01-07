<?php
/*
| ---------------------------------------------------------------
| Example Module
| ---------------------------------------------------------------
*/

class Ajax extends Core\Controller 
{

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
*/
    public function __construct()
    {
        // Normally construct the application controller
        parent::__construct(__FILE__); 
        Plexis::RenderTemplate(false);
    }
    
/*
| ---------------------------------------------------------------
| Page Functions - These are viewed by users in the frontend
| ---------------------------------------------------------------
*/
    
    public function status() 
    {
        Core\ErrorHandler::TriggerError(E_ERROR, 'test', __FILE__, __LINE__);
    }
}
?>