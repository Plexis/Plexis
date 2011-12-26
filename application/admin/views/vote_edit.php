<div class="grid_12">
    <div class="block-border">
        <div class="block-header">
            <h1>Edit Votesite</h1><span></span>
        </div>
        <form id="validate-form" class="block-content form" action="{SITE_URL}/ajax/vote" method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="{id}">
            
            <!-- Hidden Message -->
            <div id="js_message" style="display: none;"></div>
            
             <div class="_100">
                <p>
                    <label for="hostname">Hostname</label>
                    <input id="hostname" name="hostname" class="required" type="text" value="{hostname}" />
                </p>
            
                <p>
                    <label for="votelink">Vote Link</label>
                    <input id="votelink" name="votelink" class="required" type="text" value="{votelink}" />
                </p>
                
                <p>
                    <label for="image_url">Image Url</label>
                    <input id="image_url" name="image_url" type="text" value="{image_url}" />
                </p>
            </div>
            
            <div class="_25">
                <p>
                    <label for="points">Reward Points</label>
                    <input id="points" name="points" type="text" value="{points}" />
                </p>
                
                <p>
                    <label for="reset_time">Reset Time</label>
                    <select id="reset_time" name="reset_time">
                        <option value="43200" <?php if($reset_time == 43200) echo "selected='selected'"; ?>>12 Hours</option>
                        <option value="86400" <?php if($reset_time == 86400) echo "selected='selected'"; ?>>24 Hours</option>
                    </select>
                </p>
            </div>
            
            <div class="clear"></div>
            <div class="block-actions">
                <ul class="actions-left">
                    <li><a class="button red" id="reset-validate-form" href="javascript:void(0);">Undo Changes</a></li>
                </ul>
                <ul class="actions-right">
                    <li><input type="submit" class="button" value="Update Vote Site"></li>
                </ul>
            </div>
        </form>
    </div>
</div>
<!-- Javascripts -->
<script type="text/javascript">
    $().ready(function() {
        
        // Form Validation
        var validateform = $("#validate-form").validate();
		$("#reset-validate-form").click(function() {
			validateform.resetForm();
		});
        
        // ===============================================
        // bind the Vote form using 'ajaxForm' 
        $('#validate-form').ajaxForm({
            beforeSubmit: function (arr, data, options){
                $('#js_message').attr('class', 'alert loading').html('Submitting Form...').slideDown(300);
                return true;
            },
            success: post_result,
            timeout: 5000 
        });

        // Callback function for the News ajaxForm 
        function post_result(response, statusText, xhr, $form)  { 
            // Parse the JSON response
            var result = jQuery.parseJSON(response);
            if (result.success == true){
                // Display our Success message, and ReDraw the table so we imediatly see our action
                $('#js_message').attr('class', 'alert success').html(result.message);
            }else{
                $('#js_message').attr('class', 'alert ' + result.type).html(result.message);
            }
            $('#js_message').delay(5000).slideUp(300);
        }
    });
</script>