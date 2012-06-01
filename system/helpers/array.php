<?php
function array_unset_value($value, $array, $strict = false)
{
    if(!is_array($array)) return false;
    foreach($array as $k => $v)
    {
        if($strict)
        {
            if($value === $v) unset($array[$k]);
        }
        else
        {
            if($value == $v) unset($array[$k]);
        }
    }
    return $array;
}