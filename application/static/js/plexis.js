/**
 * Plexis Core JS
 * Author: Steven Wilson
 * Cookie functions from QuirksMode
 */

    // Callback function for the Install ajaxForm 
    function ajax_realm_status(div_id, loading_id)
    { 
        var post_url = url + "/ajax/realms/";
        $( div_id ).hide();
        
        // Send our Init. command
        $.ajax({
            type: "POST",
            url: post_url,
            data: {action: 'status'},
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
                var finished = parse_realm_status(result, div_id );
                if( finished == '' ) finished = '<center>No Realms Installed</center>';
                $( div_id ).html(finished).show();
                $( loading_id ).hide();
            },
            error: function(request, status, err) 
            {
                // do nothing
            }
        });
    }
    
    function parse_realm_status(result, div_id )
    {
        var count = result.length;
        var finished = '';
        for (i = 0; count > i; i++)
        {
            block = $( div_id ).html();
            block = block.replace(/\@id/i, result[i].id);
            block = block.replace(/\@name/i, result[i].name);
            block = block.replace(/\@type/i, result[i].type);
            block = block.replace(/\@status/i, result[i].status);
            block = block.replace(/\@online/i, result[i].online);
            block = block.replace(/\@alliance/i, result[i].alliance);
            block = block.replace(/\@horde/i, result[i].horde);
            block = block.replace(/\@uptime/i, result[i].uptime);
            finished += block;
        }
        return finished;
    }
    
    function setCookie(name, value, days) 
    {
        if( days )
        {
            var date = new Date();
            date.setTime( date.getTime() + (days * 24 * 60 * 60 * 1000) );
            var expires = "; expires=" + date.toGMTString();
        }
        else
        {
            var expires = "";
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    function getCookie(c_name) 
    {
        var name = c_name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) 
        {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(name) == 0)
            {
                return c.substring(name.length, c.length);
            }
        }
        return null;
    }

    function deleteCookie(name) 
    {
        setCookie(name, "", -1);
    }

