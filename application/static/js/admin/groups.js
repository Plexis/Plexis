$().ready(function() {
    var post_url = url + "/ajax/groups";

    /* Tabs */
    $("#tab-panel-1").createTabs();

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
    
    /** Form validation */
    var validateform = $("#create-form").validate();
    var validateform = $("#edit-form").validate();
    

    /** Create Group Button (Modal popup) */
    $("#create").click(function() {
        // Show form, and hide any previous messages
        $('#create-form-div').dialog({ modal: true, height: 400, width: 500 });
        $('#create-form').attr('style', '');
        $('#js_create_message').attr('style', 'display: none;');
        $('#create-form-div').attr('style', '');
    });
    
    /** Edit Form */
    $("#groups").delegate('.edit-button', 'click', function(){
        var name = $(this).attr('name');
        
        $.ajax({
            type: "POST",
            url: post_url,
            data: {action: 'getgroup', id: name},
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
                $('#edit-form').attr('style', '');
                $('#edit-form input[name=title]').val(result.group.title);
                $('#edit-form input[name=id]').val(result.group.group_id);
                $("#group_type_edit").val( "" + result.type + "").trigger('change');
            },
            error: function(request, status, err) 
            {
                $('#js_edit_message').attr('class', 'alert error').html('An error occured, Please check your error log.').slideDown(300);
            }
        });
        
        // Show form, and hide any previous messages
        $('#edit-form-div').dialog({ modal: true, height: 400, width: 500 });
        $('#js_edit_message').attr('style', 'display: none;');
        $('#edit-form-div').attr('style', '');
    });
    
    /** Delete Button */
    $("#groups").delegate('.delete-button', 'click', function(){
        var name = $(this).attr('name');
        
        $.ajax({
            type: "POST",
            url: post_url,
            data: {action: 'deletegroup', id: name},
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
                if (result.success == false)
                {
                    // Display our Success message, and ReDraw the table so we imediatly see our action
                    $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                }
                table.fnDraw();
            },
            error: function(request, status, err) 
            {
                $('#js_message').attr('class', 'alert error').html('An error occured, Please check your error log.').slideDown(300);
            }
        });
    });

    
    /** bind the Create form using 'ajaxForm' */
    $('#create-form').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#create-form').attr('style', 'display: none');
            $('#js_create_message').attr('class', 'alert loading').html('Creating...').slideDown(300);
            return true;
        },
        success: create_result,
        resetForm: true,
        timeout: 5000 
    });
    
    /** bind the Edit form using 'ajaxForm' */
    $('#edit-form').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#edit-form').attr('style', 'display: none');
            $('#js_edit_message').attr('class', 'alert loading').html('Submitting...').slideDown(300);
            return true;
        },
        success: edit_result,
        resetForm: false,
        timeout: 5000 
    });

    /** Callback function for the Add New Group ajaxForm */
    function create_result(response, statusText, xhr, $form)  
    { 
        // Parse the JSON response
        var result = jQuery.parseJSON(response);
        if (result.success == true)
        {
            // Display our Success message, and ReDraw the table so we imediatly see our action
            $('#js_create_message').attr('class', 'alert success').html(result.message);
            table.fnDraw();
        }
        else
        {
            $('#js_create_message').attr('class', 'alert ' + result.type).html(result.message);
        }
        $('#js_create_message').delay(7500).slideUp(300);
    }
    
    /** Callback function for the Edit Group ajaxForm */
    function edit_result(response, statusText, xhr, $form)  
    { 
        // Parse the JSON response
        var result = jQuery.parseJSON(response);
        if (result.success == true)
        {
            // Display our Success message, and ReDraw the table so we imediatly see our action
            $('#js_edit_message').attr('class', 'alert success').html(result.message);
            table.fnDraw();
        }
        else
        {
            $('#js_edit_message').attr('class', 'alert ' + result.type).html(result.message);
        }
        $('#js_edit_message').delay(7500).slideUp(300);
    }
});