$().ready(function() {

    /** Tipsy */
    $('select').tipsy({gravity: 'n', delayIn: 500, delayOut: 500});
    
    // ===============================================
    // bind the Config form using 'ajaxForm' 
    $('#permissions-form').ajaxForm({
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