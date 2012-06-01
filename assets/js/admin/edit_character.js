$().ready(function() {
    var post_url = Plexis.url + "/admin_ajax/characters";
    
    // Lock button
    $("a#unstuck").click(function() {
        post_action('unstuck');
    });
    
    // Delete button
    $("a#delete").click(function() {
        if ( confirm('Are you sure you want to delete this character?') )
        {
            post_action('delete');
        }
    });
    
    
    // bind the Profile form using 'ajaxForm' 
    $('#profile').ajaxForm({
        success: function (response, statusText, xhr, $form) {
            var result = jQuery.parseJSON(response);
            $('#js_message').attr('class', 'alert ' + result.type).html( result.message ).slideDown(300).delay(3000).slideUp(600);
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
                var result = jQuery.parseJSON(response);
                $('#js_message').attr('class', 'alert ' + result.type).html( result.message ).slideDown(300).delay(3000).slideUp(600);
            },
            error: function(request, status, err) 
            {
                $('#js_message').attr('class', 'alert error').html('Connection timed out. Please try again.').slideDown(300).delay(3000).slideUp(600);
            }
        });
    }
});