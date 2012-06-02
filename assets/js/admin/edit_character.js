$().ready(function() {
    var post_url = Plexis.url + "/admin_ajax/characters";
    
    // Lock button
    $("a#unstuck").click(function() {
        post_action('unstuck');
    });
    
    // Delete button
    $("a#delete").click(function() {
        $.msgbox('Are you sure you want to delete this character?', {type : 'confirm'}, function(result) {
            if (result == "Accept")
            {
                post_action('delete');
            }
        });
    });
    
    
    // bind the Profile form using 'ajaxForm' 
    $('#profile').ajaxForm({
        success: function (response, statusText, xhr, $form) {
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
        timeout: 3000 
    });
    
    // Main Ajax posting function for this page.
    function post_action(task)
    {
        // Send our Install command
        $.ajax({
            type: "POST",
            url: post_url,
            data: { action : task },
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(response) 
            {
                if(typeof result.php_error != "undefined" && result.php_error == true)
                {
                    show_php_error( result.php_error_data );
                }
                else
                {
                    $('#js_message').attr('class', 'alert ' + result.type).html( result.message ).slideDown(300).delay(3000).slideUp(600);
                }
            },
            error: function(request, status, err) 
            {
                switch(status)
                {
                    case "error":
                        $.msgbox('An error ocurred while sending the ajax request.', {type : 'error'});
                        break;
                    default:
                        $.msgbox('An error ('+ status +') ocurred while sending the ajax request', {type : 'error'});
                        break;
                }
            }
        });
    }
});