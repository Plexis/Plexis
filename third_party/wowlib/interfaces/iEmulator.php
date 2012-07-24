<?php
namespace Wowlib;

/*
| ---------------------------------------------------------------
| Emulator Interface
| ---------------------------------------------------------------
|
*/
interface iEmulator
{
    public function realmlist();
    public function fetchRealm($id);
    public function uptime($id);
    public function createAccount($username, $password, $email = NULL, $ip = '0.0.0.0');
    public function validate($username, $password);
    public function fetchAccount($id);
    public function accountExists($id);
    public function emailExists($email);
    public function accountBanned($account_id);
    public function ipBanned($ip);
    public function banAccount($id, $banreason, $unbandate = NULL, $bannedby = 'Admin', $banip = false);
    public function banAccountIp($id, $banreason, $unbandate = NULL, $bannedby = 'Admin');
    public function unbanAccount($id);
    public function unbanAccountIp($id);
    public function deleteAccount($id);
    public function expansions();
    public function expansionToText($id = 0);
    public function expansionToBit($e);
    public function numAccounts();
    public function numBannedAccounts();
    public function numInactiveAccounts();
    public function numActiveAccounts();
}