$().ready(function() {
    // Tipsy mouseovers
    $('select[name=driver]').tipsy({trigger: 'hover', gravity: 's', delayIn: 500, delayOut: 500});
    $('select[name=ra_type]').tipsy({trigger: 'hover', gravity: 's', delayIn: 500, delayOut: 500});
    $('input[name=ra_username]').tipsy({trigger: 'hover', gravity: 's', delayIn: 500, delayOut: 500});
    $('input[name=ra_urn]').tipsy({trigger: 'hover', gravity: 's', delayIn: 500, delayOut: 500});
    
    /* Tabs */
    $("#tab-panel-1").createTabs();
    
    // Form validation
    $("#edit-form").validate();
    
    // ===============================================
    // bind the Update form using 'ajaxForm' 
    $('#edit-form').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#js_message').attr('class', 'alert loading').html('Submitting Form...').slideDown(300);
            $("html, body").animate({ scrollTop: 0 }, "slow");
            return true;
        },
        success: result,
        timeout: 5000 
    });

    // Callback function for the Install ajaxForm 
    function result(response, statusText, xhr, $form)  
    { 
        // Parse the JSON response
        var result = jQuery.parseJSON(response);
        if (result.success == true)
        {
            // Display our Success message, and ReDraw the table so we imediatly see our action
            $('#edit-form').html('<div class="alert ' + result.type +'">' + result.message + '. <a href="' + Plexis.url + '/admin/realms">Click here to return.</a></div>');
        }
        else
        {
            $('#js_message').attr('class', 'alert ' + result.type).html(result.message);
        }
        $('#js_message').delay(5000).slideUp(300);
    }
});