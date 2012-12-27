<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Helpers/Html.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @author      Plexis Dev Team
 * @package     Helpers
 * @subpackage  Html
 */
 
/**
 * Creates a number of html breaks to be repeated
 *
 * @param int $count The number of breaks
 *
 * @return string A string containing the number ($count) of breaks
 */
    function br($count)
    {
        $buffer = '';
        for($i = 0; $i < $count; $i++)
            $buffer .= "<br />";
            
        return $buffer;
    }
    
/**
 * Creates a number of non-breaking spaces to be repeated
 *
 * @param int $count The number of spaces
 *
 * @return string A string containing the number ($count) of non-breaking spaces
 */
    function nbs($count)
    {
        $buffer = '';
        for($i = 0; $i < $count; $i++)
            $buffer .= "&nbsp;";
            
        return $buffer;
    }