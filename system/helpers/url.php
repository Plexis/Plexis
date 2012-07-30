<?php
/*
| ---------------------------------------------------------------
| Function: get_url_info()
| ---------------------------------------------------------------
|
| Simple way of getting the site url and url information
|
| @Return: (Object) - Return the instnace of the Controller
|
*/
if(!function_exists('get_url_info'))
{
    function get_url_info()
    {
        return load_class('Router')->get_url_info();
    }
}

/*
| ---------------------------------------------------------------
| Function: uri_segment
| ---------------------------------------------------------------
|
| This function is used to exctract a specific piece of the url.
|
| @Param: (String) $index: The zero-based index of the url part to return.
| @Return: (String / Null) String containing the specified url part,
|   null if the index it out of bounds of the array.
|
*/
if(!function_exists('uri_segment'))
{
    function uri_segment($index)
    {
        return load_class('Router')->get_uri_segement($index);
    }
}

/*
| ---------------------------------------------------------------
| Method: redirect()
| ---------------------------------------------------------------
|
| This function is used to easily redirect and refresh pages
|
| @Param: (String) $url - Where were going
| @Param: (Int) $wait - How many sec's we wait till the redirect.
| @Return: (None)
|
*/
if(!function_exists('redirect'))
{
    function redirect($url, $wait = 0)
    {
        // Check for a valid URL. If not then add our current SITE_URL to it.
        if(!preg_match('@^(mailto|ftp|http(s)?)://@i', $url))
        {
            $info = load_class('Router')->get_url_info();
            $url = trim ( $info["site_url"], "/" ) . ( ( !MOD_REWRITE ) ? "?url=/" : "/" ) . ltrim( $url, "/" );
        }

        // Check for refresh or straight redirect
        if($wait >= 1)
        {
            header("Refresh:". $wait .";url=". $url);
        }
        else
        {
            header("Location: ".$url);
            die();
        }
    }
}

/*
| ---------------------------------------------------------------
| Method: getPageContents()
| ---------------------------------------------------------------
|
| Uses either file() or CURL to get the contents of a page
|
*/
if(!function_exists('getPageContents'))
{
    function getPageContents($url, $asArray = false)
    {
        // Properly format our url
        $url = str_replace(' ', '%20', $url);
        $results = false;

        // Try to fetch the page with cURL
        if( function_exists('curl_exec') )
        {
            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, $url);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($curl_handle, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);

            // Fetch page
            $results = curl_exec($curl_handle);

            // Check if any error occured
            if(curl_errno($curl_handle)) return false;

            // Convert to array?
            if($asArray == true) $results = explode("\n", trim($results));
            curl_close($curl_handle);
        }

        // Try file() first
        elseif( ini_get('allow_url_fopen') )
        {
            $results = ($asArray == true) ? @file($url) : @file_get_contents($url, false);
        }

        // Return the page
        return $results;
    }
}
// EOF