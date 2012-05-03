<?php
/*
| ---------------------------------------------------------------
| Function: html_var_dump()
| ---------------------------------------------------------------
|
| Creates a nice looking dump of an array. Thanks to Highstrike
| http://www.php.net/manual/en/function.var-dump.php#80288
|
*/
    function html_var_dump($var, $var_name = NULL, $indent = NULL)
    {	
        // Init our empty html return
        $html = '';
        
        // Create our indent style
        $tab_line = "<span style='color:#eeeeee;'>|</span> &nbsp;&nbsp;&nbsp;&nbsp ";


        // Grab our variable type and get our text color
        $type = ucfirst(gettype($var));
        switch($type)
        {
            case "Array":
                // Count our number of keys in the array
                $count = count($var);
                $html .= "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:#a2a2a2'>$type ($count)</span><br />$indent(<br />";
                $keys = array_keys($var);
                
                // Foreach array key, we need to get the value.
                foreach($keys as $name)
                {
                    $value = $var[$name];
                    $html .= html_var_dump($value, "['$name']", $indent.$tab_line);
                }
                $html .= "$indent)<br />";
                break;
                
            case "String":
                $type_color = "<span style='color:green'>";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($var).")</span> $type_color\"$var\"</span><br />";
                break;
                
            case "Integer":
                $type_color = "<span style='color:red'>";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($var).")</span> $type_color$var</span><br />";
                break;
                
            case "Double":
                $type_color = "<span style='color:red'>"; 
                $type = "Float";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($var).")</span> $type_color$var</span><br />";
                break;
                
            case "Boolean":
                $type_color = "<span style='color:blue'>";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($var).")</span> $type_color".($var == 1 ? "TRUE":"FALSE")."</span><br />";
                break;
                
            case "NULL":
                $type_color = "<span style='color:black'>";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($var).")</span> ".$type_color."NULL</span><br />";
                break;
                
            case "Object":
                $type_color = "<span style='color:black'>";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type</span><br />";
                break;
                
            case "Resource":
                $type_color = "<span style='color:black'>";
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type</span> ".$type_color."Resource</span><br />";
                break;
                
            default:
                $html .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".@strlen($var).")</span> $var<br />";
                break;
        }

        // Return our variable dump :D
        return $html;
    }
?>