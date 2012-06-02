$().ready(function() {
    var post_url = Plexis.url + "/admin_ajax/realms";
    /**
     * DataTables
     */
    var realmtable = $('#realm-table').dataTable({
        "bServerSide": true,
        "bSortClasses": false,
        "sAjaxSource": post_url,
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            aoData.push( { "name": "action", "value": "admin" } );
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
                    show_ajax_error(status);
                }
            } );
        }
    });
    
    // ============================================
    // Uninstall Realm
    $("#realm-table").on('click', '.un-install', function(){
        var r_id = this.name;
        
        $.msgbox('Are you sure you want to uninstall realm #' + r_id, {type : 'confirm'}, function(result) {
            if (result == "Accept")
            {
                // Send our Uninstall command
                $.ajax({
                    type: "POST",
                    url: post_url,
                    data: { action : 'un-install', id : r_id },
                    dataType: "json",
                    timeout: 5000, // in milliseconds
                    success: function(result) 
                    {
                        if(typeof result.php_error != "undefined" && result.php_error == true)
                        {
                            show_php_error( result.php_error_data );
                        }
                        else
                        {
                            // Display our Success message, and ReDraw the table so we imediatly see our action
                            $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                            if (result.success == true)
                            {
                                realmtable.fnDraw();
                            }
                        }
                    },
                    error: function(request, status, err) 
                    {
                        switch(status)
                        {
                            case "error":
                                $.msgbox('An error ocurred while sending the ajax request.', {type : 'error'});
                                break;
                            default:
                                $.msgbox('An error ('+ status +') ocurred while sending the ajax request', {type : 'error'});
                                break;
                        }
                    }
                });
            }
        });
    });
    
    // ============================================
    // Make Realm Default
    $("#realm-table").on('click', '.make-default', function(){
        var r_id = this.name;
        
        // Send our make default command
        $.ajax({
            type: "POST",
            url: post_url,
            data: { action : 'make-default', id : r_id },
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
                if(typeof result.php_error != "undefined" && result.php_error == true)
                {
                    show_php_error( result.php_error_data );
                }
                else
                {
                    // Display our Success message, and ReDraw the table so we imediatly see our action
                    $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                    if (result.success == true)
                    {
                        realmtable.fnDraw();
                    }
                }
            },
            error: function(request, status, err) 
            {
                switch(status)
                {
                    case "error":
                        $.msgbox('An error ocurred while sending the ajax request.', {type : 'error'});
                        break;
                    default:
                        $.msgbox('An error ('+ status +') ocurred while sending the ajax request', {type : 'error'});
                        break;
                }
            }
        });
    });
});