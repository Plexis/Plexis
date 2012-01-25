$().ready(function() {
    check_for_updates();
});

// Function used to check for updates
function check_for_updates()
{
    $.ajax({
        type: "POST",
        url: url + "/ajax/update",
        dataType: "json",
        data: { action: "get_lastest" },
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
                    if(newest != Plexis['build'])
                    {
                        // Define some vars
                        var author = result['message'][0]['commit']['author']['name'];
                        message = message.replace('[' + newest + ']', '')
                        
                        // Show that there are updates
                        $('#update').html('<font color="green">Updates are Available! <a id="update_link" href="javascript:void(0);"'
                            + 'onclick="$(\'#update_info\').dialog({ modal: true, width: 500 });">Click Here</a> for more info</font>');
                        block = $('#update_info').html();
                        block = block.replace(/\@build/i, newest);
                        block = block.replace(/\@current/i, Plexis['build']);
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
}

function getParameterByName(name)
{
  name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
  var regexS = "[\\?&]" + name + "=([^&#]*)";
  var regex = new RegExp(regexS);
  var results = regex.exec(window.location.href);
  if(results == null)
    return "";
  else
    return decodeURIComponent(results[1].replace(/\+/g, " "));
}