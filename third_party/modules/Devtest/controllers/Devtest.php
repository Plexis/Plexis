<?php
/*
| ---------------------------------------------------------------
| Example Module
| ---------------------------------------------------------------
*/

class Devtest extends \Core\Controller 
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
    }
    
/*
| ---------------------------------------------------------------
| Page Functions - These are viewed by users in the frontend
| ---------------------------------------------------------------
*/
    
    public function index() 
    {
        global $NAME;
        $NAME = 'CAN YOU SEE ME?';
        var_dump($GLOBALS); die();
    }
}
?>