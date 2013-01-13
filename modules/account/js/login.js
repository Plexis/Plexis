/* Form validation */
$().ready(function() {
    var validateform = $("#login-form").validate(
        {
            rules: {
                username: {
                    required: true
                },
                password: {
                    required: true
                }
            }
        });
});