$().ready(function() {
    var post_url = url + "/ajax/vote";
    
    /** DataTables */
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
                "success": fnCallback
            } );
        }
    });
    
    
    /** Form Validation and Posting */
    var validateform = $("#vote").validate();
    $("#reset-form").click(function() {
        validateform.resetForm();
    });
    

    /** Create Vote Site */
    $("#create").click(function() {
        // Show form, and hide any previous messages
        $('#vote-form').dialog({ modal: true, height: 420, width: 500 });
        $('#js_news_message').attr('style', 'display: none;');
        $('#vote').attr('style', '');
    });


    /** Delete Vote Site */
    $("#data-table").delegate('.delete', 'click', function(){
        var vote_id = $(this).attr('name');
        
        if( confirm('Are you sure you want to delete vote site #' + vote_id) )
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
                    if (result.success == true)
                    {
                        // Display our Success message, and ReDraw the table so we imediatly see our action
                        $('#js_message').attr('class', 'alert success').html(result.message).slideDown(300).delay(3000).slideUp(600);
                        votetable.fnDraw();
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
    

    /** bind the Vote form using 'ajaxForm' */
    $('#vote').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#vote').attr('style', 'display: none');
            $('#js_news_message').attr('class', 'alert loading').html('Submitting Form...').slideDown(300);
        },
        success: post_result,
        clearForm: true,
        timeout: 5000 
    });

    /** Callback function for the Vote ajaxForm */
    function post_result(response, statusText, xhr, $form)  
    {
        // Parse the JSON response
        var result = jQuery.parseJSON(response);
        if (result.success == true)
        {
            // Display our Success message, and ReDraw the table so we imediatly see our action
            $('#js_news_message').attr('class', 'alert success').html(result.message);
            votetable.fnDraw();
        }
        else
        {
            $('#js_news_message').attr('class', 'alert ' + result.type).html(result.message);
        }
    }
})