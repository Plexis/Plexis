<?php

class Ajax extends Core\Controller
{
    public function __construct()
    {
        parent::__construct(__FILE__);
    }
    
    public function test($param)
    {
        echo json_encode(array($param));
    }
}