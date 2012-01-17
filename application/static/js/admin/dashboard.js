$().ready(function() {
    check_for_updates();
});

// Function used to check for updates
function check_for_updates()
{
    $.ajax({
        type: "POST",
        url: url + "/ajax/updates",
        data: {action: 'check'},
        dataType: "json",
        timeout: 5000, // in milliseconds
        success: function(result) 
        {
            // If the result is 1, we have updates!
            if (result == 1)
            {
                // Show that there are updates
                $('#update').html('<font color="green">Updates are Available! <a href="admin/update">Click Here</a> to update the CMS.</font>');
            }
            else if (result == -1)
            {
                // Show that there we cant connect to the update server
                $('#update').html('<font color="orange">Unable to connect to update server.</font>');
            }
            else
            {
                // Show that there are NO updates
                $('#update').html('Your CMS is up to date!');
            }
        },
        error: function(request, status, err) 
        {
            $('#update').html('Unable to connect to update server.');
        }
    });
}