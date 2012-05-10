$().ready(function() {
    var post_url = Plexis.url + "/admin_ajax/groups";

    /** DataTables */
    var table = $('#groups').dataTable({
        "bServerSide": true,
        "bSortClasses": false,
        "sAjaxSource": post_url,
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            aoData.push( { "name": "action", "value": "getlist" } ),
            $.ajax( {
                "dataType": 'json', 
                "type": "POST", 
                "url": sSource, 
                "data": aoData, 
                "success": fnCallback
            } );
        }
    });
    
    /** Create our vote form modal */
	var Modal = $("#groups-modal").dialog({
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
    
    /** Form validation */
    $("#groups-form").validate({
        rules: {
            title: {
                required: true,
                minlength: 3
            }
        }
    });

    /** Create Group Button (Modal popup) */
    $("#create").click(function() {

        // Show form, and hide any previous messages
        $('#js_groups_message').hide();
        $('#groups-form').show();
        
        // Set form Field Values
        $('#title').attr('value', '');
        $("#grouptype").val("2");
        $('#groupid').attr('value', '0');
        $("#formtype").attr('value', 'create');
        $('#grouptype').change();
        
        // Open the Modal Window
		Modal.dialog("option", {
			title: "Create Group"
		}).dialog("open");
        
        // Hide our close window button from view unless needed
        Modal.parent().find(".ui-dialog-buttonset").hide();
        
    });
    
    /** Edit Form */
    $("#groups").on('click', '.edit-button', function(){
        var name = this.name;
        
        $.ajax({
            type: "POST",
            url: post_url,
            data: {action: 'getgroup', id: name},
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
                // Show form, and hide any previous messages
                $('#js_groups_message').hide();
                $('#groups-form').show();
                
                // Set form Field Values
                $('#title').attr('value', result.group.title);
                $("#grouptype").val( result.type );
                $('#groupid').attr('value', name);
                $("#formtype").attr('value', 'edit');
                $('#grouptype').change();
                
                // Open the Modal Window
                Modal.dialog("option", {
                    title: "Edit Group"
                }).dialog("open");
                
                // Hide our close window button from view unless needed
                Modal.parent().find(".ui-dialog-buttonset").hide();
            },
            error: function(request, status, err) 
            {
                $('#groups-form').hide();
                $('#js_groups_message').attr('class', 'alert error').html('An error occured, Please check your error log.').slideDown(300);
            }
        });
    });
    
    /** Delete Button */
    $("#groups").on('click', '.delete-button', function(){
        var name = this.name;
        
        $.ajax({
            type: "POST",
            url: post_url,
            data: {action: 'deletegroup', id: name},
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
                // Display our Success message, and ReDraw the table so we imediatly see our action
                $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                table.fnDraw();
            },
            error: function(request, status, err) 
            {
                $('#js_message').attr('class', 'alert error').html('An error occured, Please check your error log.').slideDown(300);
            }
        });
    });

    
    /** bind the Create form using 'ajaxForm' */
    $('#groups-form').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#groups-form').hide();
            $('#js_groups_message').attr('class', 'alert loading').html('Creating...').slideDown(300);
            return true;
        },
        success: save_result,
        resetForm: true,
        timeout: 5000 
    });

    /** Callback function for the Add New Group ajaxForm */
    function save_result(response, statusText, xhr, $form)  
    { 
        // Parse the JSON response
        var result = jQuery.parseJSON(response);
        
        // Display our Success message, and ReDraw the table so we imediatly see our action
        $('#js_groups_message').attr('class', 'alert ' + result.type).html(result.message);
        Modal.parent().find(".ui-dialog-buttonset").show();
        if (result.success == true)
        {
            table.fnDraw();
        }
    }
});