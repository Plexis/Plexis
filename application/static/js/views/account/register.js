/* Form validation */
$().ready(function() {
    var validateform = $("#register-form").validate(
        {
            rules: {
                username: {
                    required: true,
                    minlength: 3,
                    maxlength: 24,
                },
                password1: {
                    required: true,
                    minlength: 3,
                    maxlength: 24
                },
                password2: {
                    required: true,
                    equalTo: "#password1"
                },
                email: {
                    required: true,
                    email: true
                },
                sa: {
                    required: true,
                    minlength: 3,
                    maxlength: 24,
                }
            }
        });
});