/* Form validation */
$().ready(function() {
    var validateform = $("#email-form").validate(
        {
            rules: {
                old_email: {
                    required: true,
                    email: true
                },
                new_email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true
                }
            }
        });
});