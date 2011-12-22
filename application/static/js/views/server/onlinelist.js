/*
 * DataTables
 */
 $().ready(function() {
 
    // First we need to add a function to the datatables library
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
    
    
    // Bind the Onlinelist table to Datatables
    var onlinelist = $('#online-table').dataTable({
        "bServerSide": true,
        "bSortClasses": false,
        "sAjaxSource": url + "/ajax/onlinelist/" + realm_id,
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            $.ajax( {
                "dataType": 'json', 
                "type": "POST", 
                "url": sSource, 
                "data": aoData, 
                "success": fnCallback
            } );
        }
    });
    
    $('#realm-changer').change(function() {
        // select realm
        realm_id = $('#realm-changer').val();
        onlinelist.fnReloadAjax( url + "/ajax/onlinelist/" + realm_id );
        $('#realm-name').html('<h3>' + $("#realm-changer option[value='" + realm_id + "']").text() + '</h3>');

    });
    
    // Set the name of the current realm of course ;)
    $('#realm-name').html('<h3>' + $("#realm-changer option[value='" + realm_id + "']").text() + '</h3>');

});