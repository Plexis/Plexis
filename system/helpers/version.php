<?php

    function verToInt($ver)
    {
        // First, convert to array by periods
        $ver_arr = explode(".", $ver);
	
        $i = 1;
        $result = 0;
        foreach($ver_arr as $vbit) 
        {
            $result += $vbit * $i;
            $i = $i / 100;
        }
    }