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
| Characters Interface
| ---------------------------------------------------------------
*/
namespace Wowlib;

interface iCharacters
{
    public function nameExists($name);
    public function fetch($id);
    public function getOnlineCount($faction = 0);
    public function getOnlineList($config = array());
    public function getCharacterList($config = array());
    public function topKills($config = array());
    public function delete($id);
    public function loginFlags();
    public function flagToBit($flag);
    public function raceToText($id);
    public function classToText($id);
    public function genderToText($id);
}