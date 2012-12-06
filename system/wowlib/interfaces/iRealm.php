<?php
/* 
| --------------------------------------------------------------
| 
| WowLib Framework for WoW Private Server CMS'
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
|
| ---------------------------------------------------------------
| Realm Interface
| ---------------------------------------------------------------
*/
namespace Wowlib;

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