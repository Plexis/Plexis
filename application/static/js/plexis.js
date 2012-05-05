/**
 * Plexis Core JS
 * Author: Steven Wilson
 */

    // Realm status default function
    function ajax_realm_status(div_id, loading_id)
    { 
        var post_url = Plexis.url + "/ajax/realms/";
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
    
    // Parses the div html and replacing the template vars
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
    
/**
 * Cookie reading / writing / deleting functions
 * Functions from QuirksMode
 */
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

/**
 * Add a cool little function to datatables that allows reloading of the table
 */
    $.fn.dataTableExt.oApi.fnReloadAjax = function ( oSettings, sNewSource, fnCallback, bStandingRedraw )
    {
        if ( typeof sNewSource != 'undefined' && sNewSource != null )
        {
            oSettings.sAjaxSource = sNewSource;
        }
        this.oApi._fnProcessingDisplay( oSettings, true );
        var that = this;
        var iStart = oSettings._iDisplayStart;

        oSettings.fnServerData( oSettings.sAjaxSource, [], function(json) {
            /* Clear the old information from the table */
            that.oApi._fnClearTable( oSettings );

            /* Got the data - add it to the table */
            var aData =  (oSettings.sAjaxDataProp !== "") ?
                that.oApi._fnGetObjectDataFn( oSettings.sAjaxDataProp )( json ) : json;

            for ( var i=0 ; i<json.aaData.length ; i++ )
            {
                that.oApi._fnAddData( oSettings, json.aaData[i] );
            }

            oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
            that.fnDraw();

            if ( typeof bStandingRedraw != 'undefined' && bStandingRedraw === true )
            {
                oSettings._iDisplayStart = iStart;
                that.fnDraw( false );
            }

            that.oApi._fnProcessingDisplay( oSettings, false );

            /* Callback user function - for event handlers etc */
            if ( typeof fnCallback == 'function' && fnCallback != null )
            {
                fnCallback( oSettings );
            }
        }, oSettings );
    }