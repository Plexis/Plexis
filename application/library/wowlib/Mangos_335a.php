<?php
/* 
| --------------------------------------------------------------
| 
| Plexis
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
*/
namespace Application\Library\Wowlib;

class Mangos_335a
{

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    function __construct()
    {
        $this->load = load_class('Loader');
        $this->DB = $this->load->database( 'RDB' );
    }
?>