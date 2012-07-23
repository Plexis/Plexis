<?php  
/*
| ---------------------------------------------------------------
| Function: parseMoney
| ---------------------------------------------------------------
|
| This function takes an amount of copper, and converts it into
| gold, silver, and copper
|
| @Param: (Int) $money - The money, in copper, to parse
| @Return (Array) An array of gold, silver, and copper
|
*/
    function parseMoney($money)
    {
        return array(
            'gold' => intval($money / 10000),
            'silver' => intval(($money % 10000) / 100),
            'copper' => intval(($money % 10000) % 100)
        );
    }
    
/*
| ---------------------------------------------------------------
| Function: parseGold
| ---------------------------------------------------------------
|
| This function does the opposite of parseMoney. Takes an array
| of gold, silver, and copper amounts and converts it to a money,
| or copper amount.
|
| @Param: (Array) $money - An array of Gold, Silver, and Copper
| @Return (Int) Returns the money amount in copper
|
*/
    function parseGold($money)
    {
        return (($money[0] * 10000) + ($money[1] * 100) + $money[2]);
    }