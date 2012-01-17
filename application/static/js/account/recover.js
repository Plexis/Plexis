/* Form validation */
$().ready(function() {
    var validateform = $("#recover-form").validate(
        {
            rules: {
                username: {
                    required: true,
                    minlength: 3,
                    maxlength: 24
                },
                email: {
                    required: true,
                    email: true
                }
            }
        });
});