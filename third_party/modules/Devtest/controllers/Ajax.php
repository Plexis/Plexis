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
    public function __construct($Module)
    {
        // Construct the parent controller, providing our module object
        parent::__construct($Module);
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