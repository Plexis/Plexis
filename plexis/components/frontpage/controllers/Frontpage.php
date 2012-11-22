<?php

use Library\Template;
use Library\View;
use Library\ViewNotFoundException;

class Frontpage
{
    public function Index()
    {
        // Load our view
        try {
            $view = new View( path(Plexis::$modulePath, "views", "main.tpl") );
            $view->Set('message', 'Hello World!');
            Template::Add($view);
        }
        catch( ViewNotFoundException $e ) {
            echo "<br /><br />". $e->getMessage();
        }
    }
}