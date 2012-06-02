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
                $('#js_message').attr('class', 'alert ' + result.type).html( result.message ).slideDown(300).delay(3000).slideUp(600);
            }
        },
        error: function () {
            $.msgbox('An error ocurred while sending the ajax request.', {type : 'error'});
        },
        timeout: 5000 
    });
});