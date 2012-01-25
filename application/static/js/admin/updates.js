$().ready(function() {
    $("#progressbar").progressbar({ value: 0, disabled: false });
    $("#details").delegate('a#update-cms', 'click', function(){
        process();
    });
});

// Function used to process the update
function process()
{
    $.ajax({
        type: "GET",
        url: update_url,
        dataType: "json",
        timeout: 5000, // in milliseconds
        success: function(result) 
        {
            // For progress bar!
            var count = result['files'].length;
            var add = (100 / count);
            var running = 0;
            
            // Globals
            var update_error = false;
            var update_message = '';
            var image_url = url + '/application/admin/img/icons/small/loading.gif';
            
            // show progress bar
            $('#update').show();
            $('#details').hide();
            
            // Set the site up for maintenace
            $.ajax({
                type: "POST",
                url: url + '/ajax/settings',
                data: { action: 'save', cfg__site_updating: 1 },
                dataType: "json",
                timeout: 5000, // in milliseconds
                success: function(result) {}
            });
            
            // Get the current update rev number
            $.each(result['files'], function(key, value){
                current = (key+1);
                $('update-state').html('<center><img src="' + image_url + '"/>Progress: '+ running +'%... Current file: ' + value['filename'] +' ('+ current +' / '+ count +')</center>');
                
                res = $.ajax({
                    type: "POST",
                    url: url + '/ajax/update',
                    data: { status: value['status'], sha: value['sha'], raw_url: value['raw_url'], filename: value['filename'] },
                    dataType: "json",
                    async: false,
                    timeout: 7000, // in milliseconds
                    success: function(result) 
                    {
                        if(result.success == true)
                        {
                            // Progress
                            running += add;
                            $("#progressbar").progressbar( 'value', running );
                        }
                        else
                        {
                            update_error = true;
                            update_message = result.message;
                            return false;
                        }
                        
                    },
                    error: function(request, status, err) 
                    {
                        // Show that there we cant connect to the update server
                        update_error = true;
                        update_message = 'Server is taking too long to respond';
                        return false;
                    }
                });
                
                // Set the site up for maintenace to false
                $.ajax({
                    type: "POST",
                    url: url + '/ajax/settings',
                    data: { action: 'save', cfg__site_updating: 0 },
                    dataType: "json",
                    timeout: 5000, // in milliseconds
                    success: function(result) {}
                });
                
                // Stop the loop!
                if(res == false) return false;
            });
            
            // Process out message back to the user based on if we had errors
            if(update_error == false)
            {
                $('#update').fadeOut(300, function(){
                    $('#update-finished').fadeIn(300);
                });
            }
            else
            {
                $('#update').fadeOut(300, function(){
                    $('#update-finished').html('<p><div class="alert error">'+ update_message +'</div></p>').fadeIn(300);
                });
            }
        },
        error: function(request, status, err) 
        {
            // Show that there we cant connect to the update server
            $('#js_message').html('<font color="orange">Unable to connect to update server.</font>');
        }
    });
}