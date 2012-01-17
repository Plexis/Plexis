$().ready(function() {
    var post_url = url + "/ajax/realms";
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
                "success": fnCallback
            } );
        }
    });
    
    // ============================================
    // Uninstall Realm
    $("#realm-table").delegate('.un-install', 'click', function(){
        var r_id = $(this).attr('name');
        
        if( confirm('Are you sure you want to uninstall realm #' + r_id) )
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
                    if (result.success == true)
                    {
                        // Display our Success message, and ReDraw the table so we imediatly see our action
                        $('#js_message').attr('class', 'alert success').html(result.message).slideDown(300).delay(3000).slideUp(600);
                        realmtable.fnDraw();
                    }
                    else
                    {
                        $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                    }
                },
                error: function(request, status, err) 
                {
                    $('#js_message').attr('class', 'alert error').html('Connection timed out. Please try again.').slideDown(300).delay(3000).slideUp(600);
                }
            });
        }
    });
    
    // ============================================
    // Make Realm Default
    $("#realm-table").delegate('.make-default', 'click', function(){
        var r_id = $(this).attr('name');
        
        // Send our make default command
        $.ajax({
            type: "POST",
            url: post_url,
            data: { action : 'make-default', id : r_id },
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
                if (result.success == true)
                {
                    // Display our Success message, and ReDraw the table so we imediatly see our action
                    $('#js_message').attr('class', 'alert success').html(result.message).slideDown(300).delay(3000).slideUp(600);
                    realmtable.fnDraw();
                }
                else
                {
                    $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                }
            },
            error: function(request, status, err) 
            {
                $('#js_message').attr('class', 'alert error').html('Connection timed out. Please try again.').slideDown(300).delay(3000).slideUp(600);
            }
        });
    });
});