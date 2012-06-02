$().ready(function() {
    var post_url = Plexis.url + "/admin_ajax/logs";
    
    /**
     * DataTables
     */
    var table = $('#logs-table').dataTable({
        "bServerSide": true,
        "bSortClasses": false,
        "sAjaxSource": post_url,
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            aoData.push( { "name": "action", "value": "get" } );
            aoData.push( { "name": "type", "value": "admin" } );
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
    
    /**
     * Delete Button
     */
    $("#logs-table").on('click', '.delete', function(){

        // Send our Uninstall command
        $.ajax({
            type: "POST",
            url: post_url,
            data: {action: 'delete', type: 'admin', id: this.name},
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
                // Display our Success message, and ReDraw the table so we imediatly see our action
                $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                if (result.success == true)
                {
                    table.fnDraw();
                }
                else
                {
                    if(typeof result.php_error != "undefined" && result.php_error == true)
                    {
                        show_php_error( result.php_error_data );
                    }
                }
            },
            error: function(request, status, err) 
            {
                show_ajax_error(status);
            }
        });
    });
    
    /**
     * Clear All Button
     */
    $("a#clear").click(function(){

        // Send our Uninstall command
        $.ajax({
            type: "POST",
            url: post_url,
            data: {action: 'delete', type: 'admin', id: 'all'},
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
                if (result.success == true)
                {
                    table.fnDraw();
                }
                else
                {
                    if(typeof result.php_error != "undefined" && result.php_error == true)
                    {
                        show_php_error( result.php_error_data );
                    }
                    else
                    {
                        $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                    }
                }
            },
            error: function(request, status, err) 
            {
                show_ajax_error(status);
            }
        });
    });

});