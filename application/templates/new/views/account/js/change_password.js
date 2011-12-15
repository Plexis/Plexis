/* Form validation */
$().ready(function() {
    var validateform = $("#password-form").validate(
        {
            rules: {
                old_password: {
                    required: true,
                },
                password1: {
                    required: true,
                },
                password2: {
                    required: true,
                    equalTo: "#password1"
                }
            }
        });
});