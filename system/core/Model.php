<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
| ---------------------------------------------------------------
| Class: Model()
| ---------------------------------------------------------------
|
| This is the Base model class. Doesnt do anything other then
| load the loader so the Database's can be loaded upon request.
|
*/
namespace System\Core;

class Model
{
    public function __construct() 
    {
        $this->load = load_class('Loader');
    }
}
