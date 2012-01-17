$().ready(function() {

    // Form Validation
    var validateform = $("#validate-form").validate();
    $("#reset-validate-form").click(function() {
        validateform.resetForm();
    });
    
    // ===============================================
    // bind the Vote form using 'ajaxForm' 
    $('#validate-form').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#js_message').attr('class', 'alert loading').html('Submitting Form...').slideDown(300);
            return true;
        },
        success: post_result,
        timeout: 5000 
    });

    // Callback function for the News ajaxForm 
    function post_result(response, statusText, xhr, $form)  
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