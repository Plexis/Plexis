<?php
namespace Wowlib;

/*
| ---------------------------------------------------------------
| Character Interface
| ---------------------------------------------------------------
|
*/
interface iCharacter
{
    public function save();
    public function isOnline();
    public function getAccountId();
    public function getName();
    public function getLevel();
    public function getClass($asText = false);
    public function getRace($asText = false);
    public function getGender($asText = false);
    public function getFaction();
    public function getXp();
    public function getMoney();
    public function getPosition();
    public function getTimePlayed();
    public function getTotalKills();
    public function getHonorPoints();
    public function getArenaPoints();
    public function getLoginFlags();
    public function hasLoginFlag($name);
    public function resetPosition();
    public function setPosition($x, $y, $z, $o, $map);
    public function setLoginFlag($name, $status);
    public function setAccountId($id);
    public function setName($name);
    public function setLevel($lvl);
    public function setXp($xp);
    public function setMoney($money);
    public function setTotalKills($kills);
    public function setHonorPoints($points);
    public function setArenaPoints($points);
}