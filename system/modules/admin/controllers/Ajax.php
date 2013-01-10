<?php

class Ajax extends Core\Controller
{
    public function __construct($Module)
    {
        // Construct the parent controller, providing our module object
        parent::__construct($Module);
    }
}