<?php
/*
| --------------------------------------------------------------
| Plexis
| --------------------------------------------------------------
| Author:       Steven Wilson 
| Author:       Tony (Syke)
| Copyright:    Copyright (c) 2011-2012, Plexis
| License:      GNU GPL v3
| ---------------------------------------------------------------
| Class: Model()
| ---------------------------------------------------------------
|
| Base Model class. Init's the database connections and realm
|
*/
namespace Application\Core;

class Model
{
    function __construct() 
    {
        $this->load = load_class('Loader');
        
        // Setup the databases and realm
        $this->DB = $this->load->database( 'DB' );
        $this->RDB = $this->load->database( 'RDB' );
        $this->realm = $this->load->realm();
    }
}
