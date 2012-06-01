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

/**
 * Adds a function to datatables that only filters a search after the
 * enter key has been hit, rather then after each keyup
 * Usage:       $('#example').dataTable().fnFilterOnReturn();
 * Author:      Jon Ranes (www.mvccms.com)
 * License:     GPL v2 or BSD 3 point style
 * Contact:     jranes /AT\ mvccms.com
 */
    $.fn.dataTableExt.oApi.fnFilterOnReturn = function (oSettings) {
        var _that = this;
     
        this.each(function (i) {
            $.fn.dataTableExt.iApiIndex = i;
            var $this = this;
            var anControl = $('input', _that.fnSettings().aanFeatures.f);
            anControl.unbind('keyup').bind('keypress', function (e) {
                if (e.which == 13) {
                    $.fn.dataTableExt.iApiIndex = i;
                    _that.fnFilter(anControl.val());
                }
            });
            return this;
        });
        return this;
    }