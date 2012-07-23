<?php
namespace Wowlib;

/*
| ---------------------------------------------------------------
| Account Interface
| ---------------------------------------------------------------
|
*/
interface iAccount
{
    public function save();
    public function getId();
    public function getUsername();
    public function getEmail();
    public function joinDate($asTimestamp = false);
    public function lastLogin($asTimestamp = false);
    public function getLastIp();
    public function isLocked();
    public function getExpansion($asText = false);
    public function setPassword($password);
    public function setUsername($username);
    public function setEmail($email);
    public function setExpansion($e);
    public function setLocked($locked);
}