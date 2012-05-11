$().ready(function() {
    var post_url = Plexis.url + "/admin_ajax/accounts";

    /** Bind the Ban button */
    $("#account-ban-button").click(function() {
        var current = this.name;
        if (current == 'ban')
        {
            $('#ban-form').dialog({ modal: true, height: 460, width: 600 });
            $('#ban').attr('style', '');
            $('#js_ban_message').attr('style', 'display: none');
        }
        else
        {
            // Start our loading message
            $('#js_message').attr('class', 'alert loading no-margin top').html('Submitting action...').slideDown(400);
            post_account_action('unban-account');
        }
    });
    
    /** Bind the Lock button */
    $("#account-lock-button").click(function() {
        var current = this.name;
        
        // Start our loading message
        $('#js_message').attr('class', 'alert loading no-margin top').html('Submitting action...').slideDown(400);
        if (current == 'lock')
        {
            post_account_action('lock-account');
        }
        else if (current == 'unlock')
        {
            post_account_action('unlock-account');
        }
    });
    
    /** Bind the Delete button */
    $("#account-delete-button").click(function() {
        if ( confirm('Are you sure you want to delete account: ' + username + ' (#' + userid + ')?') )
        {
            post_account_action('delete-account');
        }
    });

    /** Validate Forms */
    var banform = $("#ban").validate();
    var profileform = $("#profile").validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            password2: {
            equalTo: "#password1"
            }
        }
    });
    $("#reset-profile").click(function() {
        profileform.resetForm();
    });
    

    /** Bind the Ban form using 'ajaxForm' */
    $('#ban').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#ban').attr('style', 'display: none');
            $('#js_ban_message').attr('class', 'alert loading no-margin top').html('Submitting Form...').slideDown(300);
        },
        success: function(response, statusText, xhr, $form)  
        { 
            // Parse the JSON response
            var result = jQuery.parseJSON(response);
            if (result.success == true)
            {
                // Show that there are updates
                $('#account-ban-button').html('UnBan Account');
                $('#account-ban-button').attr('name', 'unban');
                
                // Update our status
                post_account_action('account-status');
                $('#js_ban_message').attr('class', 'alert success').html( result.message );
            }
            else
            {
                $('#js_ban_message').attr('class', 'alert error').html( result.message );
            }
        },
        timeout: 5000 
    });
    
    /** Bind the Profile form using 'ajaxForm' */ 
    $('#profile').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            var form = data[0];
            
            // Do some simple validation checks
            if(form.group_id.value >= level && is_super != 1) 
            {
                $('#js_profile_message').attr('class', 'alert error').html('You cannot set this users account level higher / equivalent to yourself.');
                $('#js_profile_message').slideDown(600).delay(4000).slideUp(600);
                return false;
            }
            else
            {
                $('#js_profile_message').attr('class', 'alert loading').html('Submitting Form').slideDown(300);
                return true;
            }
        },
        success: function (response, statusText, xhr, $form)  
        { 
            // Parse the JSON response
            var result = jQuery.parseJSON(response);
            if (result.success == true)
            {
                // Account updated successfully
                $('#js_profile_message').attr('class', 'alert success').html(result.message);
                $('#password1').attr('value', '');
                $('#password2').attr('value', '');
            }
            else
            {
                // General errors
                $('#js_profile_message').attr('class', 'alert ' + result.type).html(result.message);
            }
            $('#js_profile_message').delay(4000).slideUp(600);
        },
        timeout: 3000 
    });
    
    /** Date picker for the ban form */
    $( "#unbandate" ).datepicker();
    
    // Main Ajax posting function for this page.
    function post_account_action(task)
    {
        // Send our Install command
        $.ajax({
            type: "POST",
            url: post_url,
            data: { action : task, id : userid },
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
               switch(task)
                {
                    case "unban-account":
                        if (result.success == true){
                            // UnBan was a success
                            $('#js_message').attr('class', 'alert success no-margin top').html( result.message );
                            $('#account-ban-button').html('Ban Account');
                            $('#account-ban-button').attr('name', 'ban');
                        }else{
                            // General error
                            $('#js_message').attr('class', 'alert error no-margin top').html( result.message );
                        }
                        // Update our status and display our message
                        post_account_action('account-status');
                        $('#js_message').delay(2000).slideUp(600);
                        break;
                        
                    case "lock-account":
                        if (result.success == true){
                            // Locking of account successful
                            $('#account-lock-button').html('Unlock Account');
                            $('#account-lock-button').attr('name', 'unlock');
                            $('#js_message').attr('class', 'alert success no-margin top').html( result.message );
                        }else{
                            // General error
                            $('#js_message').attr('class', 'alert error no-margin top').html( result.message );
                        }
                        
                        // Update our status and display our message
                        post_account_action('account-status');
                        $('#js_message').delay(2000).slideUp(600);
                        break;
                        
                    case "unlock-account":
                        if (result.success == true){
                            // Unlocking of account successful
                            $('#account-lock-button').html('Lock Account');
                            $('#account-lock-button').attr('name', 'lock');
                            $('#js_message').attr('class', 'alert success no-margin top').html( result.message );
                        }else{
                            // General error
                            $('#js_message').attr('class', 'alert error no-margin top').html( result.message );
                        }
                        
                        // Update our status and display our message
                        post_account_action('account-status');
                        $('#js_message').delay(2000).slideUp(600);
                        break;
                        
                    case "account-status":
                        $('#account_status').html( result.message );
                        break;
                        
                    case "delete-account":
                        if (result.success == true){
                            // Delete successful
                            alert( result.message );
                            window.location = url + "/admin/users";
                        }else{
                            // Error
                            alert( result.message );
                        }
                        break;
                }
            },
            error: function(request, status, err) 
            {
                $('#js_message').attr('class', 'alert error').html('Connection timed out. Please try again.').slideDown(300).delay(3000).slideUp(600);
            }
        });
    }
});