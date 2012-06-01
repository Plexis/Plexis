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
            aoData.push( { "name": "type", "value": "errors" } );
            $.ajax( {
                "dataType": 'json', 
                "type": "POST", 
                "url": sSource, 
                "data": aoData, 
                "success": fnCallback
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
            data: {action: 'delete', type: 'errors', id: this.name},
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
            },
            error: function(request, status, err) 
            {
                $('#js_message').attr('class', 'alert error').html('Connection timed out. Please try again.').slideDown(300).delay(3000).slideUp(600);
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
            data: {action: 'delete', type: 'errors', id: 'all'},
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