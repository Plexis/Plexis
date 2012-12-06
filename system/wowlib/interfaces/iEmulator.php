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
| Emulator Interface
| ---------------------------------------------------------------
*/
namespace Wowlib;

interface iEmulator
{
    /* Main Methods */
    public function fetchAccount($id);
    public function getAccountList($config = array());
    public function fetchRealm($id);
    public function getRealmlist($config = array());
    public function uptime($id);
    public function createAccount($username, $password, $email = NULL, $ip = '0.0.0.0');
    public function validate($username, $password);
    public function login($username, $password);
    public function accountExists($id);
    public function emailExists($email);
    public function accountBanned($account_id);
    public function ipBanned($ip);
    public function banAccount($id, $banreason, $unbandate = NULL, $bannedby = 'Admin', $banip = false);
    public function banAccountIp($id, $banreason, $unbandate = NULL, $bannedby = 'Admin');
    public function unbanAccount($id);
    public function unbanAccountIp($id);
    public function deleteAccount($id);
    public function expansionLevel();
    public function expansionToBit($e);
    public function numAccounts();
    public function numBannedAccounts();
    public function numInactiveAccounts();
    public function numActiveAccounts();

    /* Helper Methods */
    public function getConfig();
    public function getColumnById($table, $col);
    public function getDB();
}