<?php
/**
 * Plexis Content Management System
 *
 * @file        System/Helpers/Array.php
 * @copyright   2011-2012, Plexis Dev Team
 * @license     GNU GPL v3
 * @author      Plexis Dev Team
 * @package     Helpers
 * @subpackage  Array
 */
 
/**
 * Formats an array into a jQuery.datatables format
 *
 */
    function arraySortKeysByLength($array, $order = SORT_ASC)
    {
        // Sort keys
        $keys = array_keys($array);
        
        if($order == SORT_ASC)
            usort($array,'_sortByLengthAsc');
        else
            usort($array,'_sortByLengthDesc');
        
        $newArr = array();
        foreach($keys as $index => $key)
            $newArr[$key] = $array[$key];
        
        return $newArr;
    }
    
    // http://php.net/manual/en/function.sort.php#99419
    function arraySort($array, $on, $order = SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) < 1) 
            return $array;
            
        foreach ($array as $k => $v) 
        {
            if (is_array($v)) 
            {
                foreach ($v as $k2 => $v2) 
                {
                    if ($k2 == $on)
                        $sortable_array[$k] = $v2;
                }
            } 
            else
                $sortable_array[$k] = $v;
        }

        switch ($order) 
        {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v)
            $new_array[$k] = $array[$k];

        return $new_array;
    }
    
    function arraySortByLength(array $array, $order = SORT_ASC)
    {
        if($order == SORT_ASC)
            usort($array,'_sortByLengthAsc');
        else
            usort($array,'_sortByLengthDesc');
        return $array;
    }
    
    function arraySortByOccurance(array $array, $on, $order = SORT_ASC)
    {
        
    }
    
    function _sortByLengthDesc($a, $b)
    {
        if($a == $b) return 0;
        return (strlen($a) > strlen($b) ? -1 : 1);
    }
    
    function _sortByLengthAsc($a, $b)
    {
        if($a == $b) return 0;
        return (strlen($a) < strlen($b) ? -1 : 1);
    }