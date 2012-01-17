$().ready(function() {
    var post_url = url + "/ajax/modules";
    
    /**
     * DataTables
     */
    var modtable = $('#module-table').dataTable({
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
                "success": fnCallback
            } );
        }
    });
    
    // ============================================
    // Install Module
    $("#module-table").delegate('.install', 'click', function(){
        var name = $(this).attr('name');
        
        // Show form, and hide any previous messages
        $('#install-form').dialog({ modal: true, height: 300, width: 500 });
        $('#install').removeAttr( "style" );
        $('#js_install_message').attr('style', 'display: none;');
        $('input[name=module]').val( name );
        $('input[name=function]').val( 'index' );
        $('input[name=uri]').val( 'seg1/seg2' );
        
    });
    
    // ============================================
    // Uninstall Module
    $("#module-table").delegate('.un-install', 'click', function(){
        var name = $(this).attr('name');
        
        if( confirm('Are you sure you want to uninstall Module "' + name + '"?') )
        {
            // Send our uninstall command
            $.ajax({
                type: "POST",
                url: post_url,
                data: { action : 'un-install', name : name },
                dataType: "json",
                timeout: 5000, // in milliseconds
                success: function(result) 
                {
                    if (result.success == true)
                    {
                        // Display our Success message, and ReDraw the table so we imediatly see our action
                        $('#js_message').attr('class', 'alert success').html(result.message).slideDown(300).delay(3000).slideUp(600);
                        modtable.fnDraw();
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
    
    // ===============================================
    // bind the Install form using 'ajaxForm' 
    $('#install').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#install').attr('style', 'display: none');
            $('#js_install_message').attr('class', 'alert loading').html('Submitting Form...').slideDown(300);
            $( "#install-form" ).dialog( "option", "height", 150 );
        },
        success: install_result,
        clearForm: true,
        timeout: 5000 
    });

    // Callback function for the News ajaxForm 
    function install_result(response, statusText, xhr, $form)  
    {
        // Parse the JSON response
        var result = jQuery.parseJSON(response);
        if (result.success == true)
        {
            // Display our Success message, and ReDraw the table so we imediatly see our action
            $('#js_install_message').attr('class', 'alert success').html(result.message);
            modtable.fnDraw();
        }
        else
        {
            $('#js_install_message').attr('class', 'alert ' + result.type).html(result.message);
        }
    }
});