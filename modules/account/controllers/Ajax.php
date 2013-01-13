<?php

class Ajax extends Core\Controller
{
    public function __construct($Module)
    {
        // Construct the parent controller, providing our module object
        parent::__construct($Module);
    }
    
    public function test($param)
    {
        echo json_encode(array($param));
    }
}