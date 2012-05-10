$().ready(function() {
    // Check for updates
    $.ajax({
        type: "POST",
        url: Plexis.url + "/admin_ajax/update",
        dataType: "json",
        data: { action: "get_latest" },
        timeout: 5000, // in milliseconds
        success: function(result) 
        {
            if(result.success == true)
            {
                // Get the current update rev number
                var message = result['message'][0]['commit']['message'];
                p = /([0-9]+)/;
                update = p.exec(message);
                
                // If the update is null, then we cant get a good read
                if(update == null)
                {
                    // Show that there was an error
                    $('#update').html('<font color="orange">Error fetching newest build number</font>');
                }
                else
                {
                    // Compare the current build with the update
                    newest = update[0];
                    if(newest != Build)
                    {
                        // Define some vars
                        var author = result['message'][0]['author']['login'];
                        message = message.replace('[' + newest + ']', '')
                        
                        // Show that there are updates
                        $('#update').html('<font color="green">Updates are Available! <a id="update_link" href="javascript:void(0);"'
                            + 'onclick="$(\'#update_info\').dialog({ modal: true, width: 500 });">Click Here</a> for more info</font>');
                        block = $('#update_info').html();
                        block = block.replace(/\@build/i, newest);
                        block = block.replace(/\@current/i, Build);
                        block = block.replace(/\@message/i, message);
                        block = block.replace(/\@author/i, author);
                        $('#update_info').html( block );
                    }
                    else
                    {
                        $('#update').html('Your CMS is up to date!');
                    }
                }
            }
            else
            {
                $('#update').html('<font color="orange">' + result.message + '</font>');
            }
        },
        error: function(request, status, err) 
        {
            // Show that there we cant connect to the update server
            $('#update').html('<font color="orange">Unable to connect to update server.</font>');
        }
    });
});