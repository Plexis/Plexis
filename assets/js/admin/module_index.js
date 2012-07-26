$().ready(function() {
    var post_url = Plexis.url + "/admin_ajax/modules";
    
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
    
    /** Create our module form modal */
	var Modal = $("#install-modal").dialog({
		autoOpen: false,  
		modal: true, 
		width: 600,
        resizable: false,
		buttons: [{
			text: "Close", 
			click: function() {
				$( this ).dialog( "close" );
			}
		}]
	});
    
    /**
     * Form Validation and Posting
     */
    $("#install-form").validate();
    
    // ============================================
    // Install Module
    $("#module-table").on('click', '.install', function(){
        var name = this.name;
        
        // Show form, and hide any previous messages
        $('#js_install_message').hide();
        $('#install-form').show();
        $('input[name=module]').val( name );
        $('input[name=function]').val( '' );
        $('input[name=uri]').val( '' );
        
        // Open the Modal Window
		Modal.dialog("option", {
			title: "Install Module"
		}).dialog("open");
        
        // Hide our close window button from view unless needed
        Modal.parent().find(".ui-dialog-buttonset").hide(); 
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
                    url: post_url,
                    data: { action : 'un-install', name : name },
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
                                modtable.fnDraw();
                            }
                        }
                    },
                    error: function(request, status, err) 
                    {
                        $('#js_message').attr('class', 'alert error').html('Connection timed out. Please try again.').slideDown(300).delay(3000).slideUp(600);
                    }
                });
            }
        });
    });
    
    // ===============================================
    // bind the Install form using 'ajaxForm' 
    $('#install-form').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#install-form').hide();
            $('#js_install_message').attr('class', 'alert loading').html('Submitting Form...').slideDown(300);
        },
        success: function (response)  
        {
            // Parse the JSON response
            var result = jQuery.parseJSON(response);
            
            if(typeof result.php_error != "undefined" && result.php_error == true)
            {
                show_php_error( result.php_error_data );
            }
            else
            {
                // Display our Success message, and ReDraw the table so we imediatly see our action
                $('#js_install_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300);
                if (result.success == true)
                {
                    modtable.fnDraw();
                }
            }
            
            // Show close button
            Modal.parent().find(".ui-dialog-buttonset").show();
        },
        clearForm: true,
        timeout: 5000 
    });
});