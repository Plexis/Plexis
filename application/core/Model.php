<?php
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
