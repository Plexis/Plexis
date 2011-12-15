/* Form validation */
$().ready(function() {
    var validateform = $("#recover-form").validate(
        {
            rules: {
                answer: {
                    required: true,
                    minlength: 3,
                    maxlength: 24
                }
            }
        });
});