<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Helpers/Validation.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @author      Plexis Dev Team
 * @package     Helpers
 * @subpackage  Validation
 */
    
/**
 * Checks an IP address, returning whether its a valid, Non-Private IP.
 *
 * @param string $ip The ip address to check.
 *
 * @return bool Returns true if the given IP address is a valid, Non-Private IP, false otherwise
 */
    function isValidIp($ip)
    {
        // Trim the ip address
        $ip = trim($ip);
        if(!empty($ip) && ip2long($ip) != -1)
        {
            $reserved_ips = array(
                array('0.0.0.0','2.255.255.255'),
                array('10.0.0.0','10.255.255.255'),
                array('127.0.0.0','127.255.255.255'),
                array('169.254.0.0','169.254.255.255'),
                array('172.16.0.0','172.31.255.255'),
                array('192.0.2.0','192.0.2.255'),
                array('192.168.0.0','192.168.255.255'),
                array('255.255.255.0','255.255.255.255')
            );

            foreach($reserved_ips as $r)
            {
                $min = ip2long($r[0]);
                $max = ip2long($r[1]);
                if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
            }
            return true;
        }
        return false;
    }