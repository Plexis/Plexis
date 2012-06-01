$().ready(function() {
    /* Tabs */
    $("#tab-panel-1").createTabs();
    
    /* Form validation */
    var validateform = $("#config-form").validate();
    
    // ===============================================
    // bind the Config form using 'ajaxForm' 
    $('#config-form').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#js_message').attr('class', 'alert loading').html('Submitting config settings...').slideDown(300);
            $("html, body").animate({ scrollTop: 0 }, "slow");
            return true;
        },
        success: save_result,
        timeout: 5000 
    });

    // Callback function for the Config ajaxForm 
    function save_result(response, statusText, xhr, $form)  
    { 
        // Parse the JSON response
        var result = jQuery.parseJSON(response);
        if (result.success == true)
        {
            // Display our Success message, and ReDraw the table so we imediatly see our action
            $('#js_message').attr('class', 'alert success').html(result.message);
        }
        else
        {
            $('#js_message').attr('class', 'alert ' + result.type).html(result.message);
        }
        $('#js_message').delay(5000).slideUp(300);
    }
});