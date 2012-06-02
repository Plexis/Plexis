$().ready(function() {
    var post_url = Plexis.url + "/admin_ajax/templates";
    
    /**
     * DataTables
     */
    var table = $('#templates-table').dataTable({
        "bServerSide": true,
        "bSortClasses": false,
        "sAjaxSource": post_url,
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            aoData.push( { "name": "action", "value": "getlist" } );
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
    // Install
    $("#templates-table").on('click', '.install', function(){
        var name = this.name;
        
        // Send our Install command
        $.ajax({
            type: "POST",
            url: post_url,
            data: {action: 'install', id: name},
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
                        table.fnDraw();
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
    
    // ============================================
    // Uninstall
    $("#templates-table").on('click', '.un-install', function(){
        var name = this.name;

        // Send our Uninstall command
        $.ajax({
            type: "POST",
            url: post_url,
            data: {action: 'un-install', id: name},
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
                        table.fnDraw();
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
    
    // ============================================
    // Make Default
    $("#templates-table").on('click', '.make-default', function(){
        var name = this.name;
        
        // Send our Install command
        $.ajax({
            type: "POST",
            url: post_url,
            data: {action: 'make-default', id: name},
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
                        table.fnDraw();
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