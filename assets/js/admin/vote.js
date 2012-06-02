$().ready(function() {
    var post_url = Plexis.url + "/admin_ajax/vote";
    
    /** Bind the vote table with DataTables */
    var votetable = $('#data-table').dataTable({
        "bServerSide": true,
        "bSortClasses": false,
        "sAjaxSource": post_url,
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
                    show_ajax_error(status);
                }
            } );
        }
    });
    
    /** Create our vote form modal */
	Modal = $("#vote-form").dialog({
		autoOpen: false,  
		modal: true, 
		width: 500,
        resizable: false,
		buttons: [{
			text: "Close", 
			click: function() {
				$( this ).dialog( "close" );
			}
		}]
	});
    
    
    /** Form Validation and Posting */
    var validateform = $("#vote").validate();
    $("#reset-form").click(function() {
        validateform.resetForm();
    });
    

    /** Create Vote Site */
    $("#create").click(function() {
        // Open the Modal Window
		Modal.dialog("option", {
			title: "Add New Vote Site"
		}).dialog("open");
        
        // Hide our close window button from view unless needed
        Modal.parent().find(".ui-dialog-buttonset").hide();
        
        // Reset form submit button value
        $('#formtype').attr('value', 'create');
        $('#hostname').attr('value', '');
        $('#votelink').attr('value', '');
        $('#image_url').attr('value', '');
        $('#points').attr('value', '');
        $('#form-submit').attr('value', 'Create');
        
        // Make sure we hide the old message, and display the form
        $('#js_news_message').hide();
        $('#vote').show();
    });

    /** Edit Vote Site */
    $("#data-table").on('click', '.edit', function(){
        var vote_id = this.name;
        
        // First we ajax to get the votesite information
        $.ajax({
            type: "POST",
            url: post_url,
            data: { action : 'get', id : vote_id },
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
                    if (result.success == true)
                    {
                        // Less typing
                        result = result.message;
                        
                        // Make sure we hide the old message, and display the form
                        $('#js_news_message').hide();
                        $('#vote').show();
                        
                        // Set the form values
                        $('#formtype').attr('value', 'edit');
                        $('#vote_id').attr('value', vote_id);
                        $('#hostname').attr('value', result.hostname);
                        $('#votelink').attr('value', result.votelink);
                        $('#image_url').attr('value', result.image_url);
                        $('#points').attr('value', result.points);
                        $('#reset_time').val(result.reset_time);
                        $('#reset_time').change();
                        $('#form-submit').attr('value', 'Update Vote Site');
                        
                        // Open the Modal Window
                        Modal.dialog("option", {
                            title: "Edit Vote Site"
                        }).dialog("open");
                        
                        // Hide our close window button from view unless needed
                        Modal.parent().find(".ui-dialog-buttonset").hide();
                    }
                    else
                    {
                        $.msgbox( result.message, {type: 'alert'} );
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

    /** Delete Vote Site */
    $("#data-table").on('click', '.delete', function(){
        var vote_id = this.name;
        
        $.msgbox('Are you sure you want to delete vote site #' + vote_id, {type : 'confirm'}, function(result) {
            if (result == "Accept")
            {
                // Send our delete command
                $.ajax({
                    type: "POST",
                    url: post_url,
                    data: { action : 'delete', id : vote_id },
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
                            $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown('slow').delay(3000).slideUp('slow');
                            if (result.success == true)
                            {
                                votetable.fnDraw();
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
    

    /** bind the Vote form using 'ajaxForm' */
    $('#vote').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#js_news_message').attr('class', 'alert loading').html('Submitting Form...');
            $('#vote').fadeOut(300, function(){
                $('#js_news_message').fadeIn(300);
            });
            return true;
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
                $('#js_news_message').attr('class', 'alert ' + result.type).html(result.message);
                if (result.success == true)
                {
                    votetable.fnDraw();
                }
            }
            
            // Reshow the button to close the window!
            Modal.parent().find(".ui-dialog-buttonset").show();
        },
        error: function () {
            $.msgbox('An error ocurred while sending the ajax request.', {type : 'error'});
        },
        clearForm: true,
        timeout: 5000 
    });
})