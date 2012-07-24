<?php
namespace Wowlib;

/*
| ---------------------------------------------------------------
| Realm Interface
| ---------------------------------------------------------------
|
*/
interface iRealm
{
    public function save();
    public function getName();
    public function getAddress();
    public function getPort();
    public function getType();
    public function getPopulation();
    public function getBuild();
    public function getStatus($timeout = 3);
    public function setName($name);
    public function setAddress($address);
    public function setPort($port);
    public function setType($icon);
}