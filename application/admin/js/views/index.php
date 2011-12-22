<script type="text/javascript">
$(window).load(function() {
    check_for_updates();
    
    // Function used to check for updates
    function check_for_updates(){

        //use ajax to run the check
        $.post( url + "/ajax/updates", { action : "check"},
            function(result){
                // If the result is 1, we have updates!
                if (result == 1){
                    // Show that there are updates
                    $('#update').html('<font color="green">Updates are Available! <a href="admin/update">Click Here</a> to update the CMS.</font>');
                }else if (result == -1){
                    // Show that there we cant connect to the update server
                    $('#update').html('<font color="orange">Unable to connect to update server.</font>');
                }else{
                    // Show that there are NO updates
                    $('#update').html('Your CMS is up to date!');
                }
            })
        
        // Log errors
        .error(function() {
            console.log(arguments, 'Error');
            $('#update').html('Unable to connect to update server.');
        });
    }
});
</script>