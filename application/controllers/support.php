<?php
class Support extends Application\Core\Controller 
{
    function __construct()
    {
        parent::__construct();
    }

    function index() 
    {
        show_404();
    }

}
// EOF