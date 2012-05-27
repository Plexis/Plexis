$().ready(function() {
    // Tipsy mouseovers
    $('select[name=driver]').tipsy({trigger: 'hover', gravity: 's', delayIn: 500, delayOut: 500});
    $('select[name=ra_type]').tipsy({trigger: 'hover', gravity: 's', delayIn: 500, delayOut: 500});
    $('input[name=ra_username]').tipsy({trigger: 'hover', gravity: 's', delayIn: 500, delayOut: 500});
    $('input[name=ra_urn]').tipsy({trigger: 'hover', gravity: 's', delayIn: 500, delayOut: 500});
    
    /* Tabs */
    $("#tab-panel-1").createTabs();
    
    // Form validation
    $("#install-form").validate({
        rules: {
            name: {
                required: true,
                minlength: 3
            },
            address: {
                required: true
            },
            port: {
                required: true,
                minlength: 2
            },
            max_players: {
                required: true
            },
            c_address: {
                required: true,
                minlength: 8
            },
            c_port: {
                required: true,
                minlength: 2
            },
            c_address: {
                required: true,
                minlength: 8
            },
            c_username: {
                required: true
            },
            c_password: {
                required: true
            },
            c_database: {
                required: true
            },
            w_address: {
                required: true,
                minlength: 8
            },
            w_port: {
                required: true,
                minlength: 2
            },
            w_address: {
                required: true,
                minlength: 8
            },
            w_username: {
                required: true
            },
            w_password: {
                required: true
            },
            w_database: {
                required: true
            },
        }
    });
    
    // ===============================================
    // bind the Install form using 'ajaxForm' 
    $('#install-form').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#js_message').attr('class', 'alert loading').html('Submitting Form...').slideDown(300);
            $("html, body").animate({ scrollTop: 0 }, "slow");
            return true;
        },
        success: function (response, statusText, xhr, $form)  
        { 
            // Parse the JSON response
            var result = jQuery.parseJSON(response);
            if (result.success == true)
            {
                // Display our Success message, and ReDraw the table so we imediatly see our action
                $('#install-form').html('<div class="alert ' + result.type +'">' + result.message + '. <a href="' + url + '/admin/realms">Click here to return.</a></div>');
            }
            else
            {
                $('#js_message').attr('class', 'alert ' + result.type).html(result.message);
            }
            $('#js_message').delay(5000).slideUp(300);
        },
        timeout: 5000 
    });
});