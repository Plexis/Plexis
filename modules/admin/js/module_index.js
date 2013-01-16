$().ready(function() {
    /**
     * DataTables
     */
    var modtable = $('#module-table').dataTable({
        "bServerSide": true,
        "bSortClasses": false,
        "sAjaxSource": Globals.Url + "/admin/modules/getlist",
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            $.ajax( {
                "dataType": 'json', 
                "type": "POST", 
                "url": sSource, 
                "data": aoData, 
                "success": function (result, status, jqXHR) {
                    if(typeof result.php_error != "undefined" && result.php_error == true)
                    {
                        show_php_error(result.php_error_data);
                    }
                    else
                    {
                        fnCallback(result, status, jqXHR);
                    }
                },
                "error": function(request, status, err) {
                    Plexis.HandleAjaxError(request, status, err);
                }
            } );
        },
        "oLanguage": {
          "sZeroRecords": "Plexis could not find any modules... By golly this is impossible D:"
        },
        "iDisplayLength": 100,
        "aoColumns": [
            null,
            null,
            { "bSearchable": false },
            { "bSearchable": false },
            { "bSearchable": false },
            { "bSearchable": false }
        ]
    });
    
    // Hide useless data table stuff
    $(".dataTables_length").hide();
    //$(".dataTables_filter").hide();
    $(".dataTables_paginate").hide();
    $(".dataTables_info").hide();
    
    // ============================================
    // Install Module
    $("#module-table").on('click', '.install', function(){
        var name = this.name;
        
        // Send our uninstall command
        $.ajax({
            type: "POST",
            url: Globals.Url + "/admin/modules/install",
            data: { name : name },
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
                // Display our Success message, and ReDraw the table so we imediatly see our action
                $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                if (result.success == true)
                {
                    modtable.fnDraw();
                }
            },
            error: function(request, status, err) 
            {
                Plexis.HandleAjaxError(request, status, err);
            }
        });
    });
    
    // ============================================
    // Uninstall Module
    $("#module-table").on('click', '.un-install', function(){
        var name = this.name;
        
        $.msgbox('Are you sure you want to uninstall Module "' + name + '"?', {type : 'confirm'}, function(result) {
            if (result == "Accept")
            {
                // Send our uninstall command
                $.ajax({
                    type: "POST",
                    url: Globals.Url + "/admin/modules/uninstall",
                    data: { name : name },
                    dataType: "json",
                    timeout: 5000, // in milliseconds
                    success: function(result) 
                    {
                        // Display our Success message, and ReDraw the table so we imediatly see our action
                        $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                        if (result.success == true)
                        {
                            modtable.fnDraw();
                        }
                        else
                        {
                        
                        }
                    },
                    error: function(request, status, err) 
                    {
                        Plexis.HandleAjaxError(request, status, err);
                    }
                });
            }
        });
    });
});