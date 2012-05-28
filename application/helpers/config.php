<?php
/*
| ---------------------------------------------------------------
| Method: config()
| ---------------------------------------------------------------
|
| This function is used to return a config value from a config
| file.
|
| @Param: (String) $item - The config item we are looking for
| @Param: (Mixed) $type - Name of the config variables, this is set 
|   when you load the config, defaults are Core, App and Mod
| @Return: (Mixed) - Returns the config vaule of $item
|
*/
if(!function_exists('config'))
{
    function config($item, $type = 'App')
    {
        return load_class('Config')->get($item, $type);
    }
}

/*
| ---------------------------------------------------------------
| Method: config_set()
| ---------------------------------------------------------------
|
| This function is used to set site config values. This does not
| set core, or database values.
|
| @Param: (String) $item - The config item we are setting a value for
| @Param: (Mixed) $value - the value of $item
| @Param: (Mixed) $name - The name of this config variables container
| @Return: (None)
|
*/
if(!function_exists('config_set'))
{
    function config_set($item, $value, $name = 'App')
    {
        load_class('Config')->set($item, $value, $name);
    }
}

/*
| ---------------------------------------------------------------
| Method: config_save()
| ---------------------------------------------------------------
|
| This function is used to save site config values to the condig.php. 
| *Warning - This will remove any and ALL comments in the config file
|
| @Param: (Mixed) $name - Which config are we saving? App? Core? Module?
| @Return: (None)
|
*/
if(!function_exists('config_save'))
{
    function config_save($name)
    {
        return load_class('Config')->save($name);
    }
}

/*
| ---------------------------------------------------------------
| Method: config_load()
| ---------------------------------------------------------------
|
| This function is used to get all defined variables from a config
| file.
|
| @Param: (String) $file - full path and filename to the config file being loaded
| @Param: (Mixed) $name - The name of this config variables, for later access. Ex:
|   if $name = 'test', the to load a $var -> config( 'var', 'test');
| @Param: (String) $array - If the config vars are stored in an array, whats
|   the array variable name?
| @Return: (Bool)
|
*/
if(!function_exists('config_load'))
{
    function config_load($file, $name, $array = FALSE)
    {
        return load_class('Config')->load($file, $name, $array);
    }
}