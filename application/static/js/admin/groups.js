$().ready(function() {
    var post_url = url + "/ajax/groups";

    /**
     * DataTables
     */
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
    
    /* Form validation */
    var validateform = $("#form").validate();
    
    // ============================================
    // Create Group
    $("#create").click(function() {
        // Show form, and hide any previous messages
        $('#form').dialog({ modal: true, height: 400, width: 500 });
        $('#js_modal_message').attr('style', 'display: none;');
        $('#form').attr('style', '');
    });
    
    // ===============================================
    // bind the Model form using 'ajaxForm' 
    $('#create-form').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#form').attr('style', 'display: none');
            $('#js_modal_message').attr('class', 'alert loading').html('Creating...').slideDown(300);
            return true;
        },
        success: save_result,
        timeout: 5000 
    });

    // Callback function for the Add New ajaxForm 
    function save_result(response, statusText, xhr, $form)  
    { 
        // Parse the JSON response
        var result = jQuery.parseJSON(response);
        if (result.success == true)
        {
            // Display our Success message, and ReDraw the table so we imediatly see our action
            $('#js_message').attr('class', 'alert success').html(result.message);
            table.fnDraw();
        }
        else
        {
            $('#js_message').attr('class', 'alert ' + result.type).html(result.message);
        }
        $('#js_message').delay(7500).slideUp(300);
    }
});
