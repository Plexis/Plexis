<!-- Button -->
<div>
    <a id="create" href="javascript:void(0);" class="button">Add New Vote Site</a>
</div>

<!-- News List Table -->
<div class="block-border">
    <div class="block-header">
        <h1>Vote Sites</h1><span></span>
    </div>
    <div class="block-content">
        <div id="js_message" style="display: none;"></div>
        <table id="data-table" class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hostname</th>
                    <th>Vote Link</th>
                    <th>Points</th>
                    <th>Reset Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Hidden Create / Edit form -->
<div id="vote-form" title="Create Vote Site" style="display: none;">
    <div id="js_news_message" style="display: none;"></div>
    <form id="vote" class="form" action="{SITE_URL}/ajax/vote" method="post">
        <input type="hidden" name="action" value="create">
        <div class="_100">
            <p>
                <label for="hostname">Hostname</label>
                <input id="hostname" name="hostname" class="required" type="text" value="" />
            </p>
        
            <p>
                <label for="votelink">Vote Link</label>
                <input id="votelink" name="votelink" class="required" type="text" value="" />
            </p>
            
            <p>
                <label for="image_url">Image Url</label>
                <input id="image_url" name="image_url" type="text" value="" />
            </p>
        </div>
        
        <div class="_25">
            <p>
                <label for="points">Reward Points</label>
                <input id="points" name="points" type="text" value="0" />
            </p>
            
            <p>
                <label for="reset_time">Reset Time</label>
                <select id="reset_time" name="reset_time">
                    <option value="43200">12 Hours</option>
                    <option value="86400">24 Hours</option>
                </select>
            </p>
        </div>

        <div class="_100">
            <input id="submit" type="submit" class="button" style="width: 150px; text-align: center; margin: 10px 150px; " value="Submit">
        </div>

    </form>
</div>

<!-- DataTables for Ajax loading -->
<script src="{TEMPLATE_URL}/js/mylibs/jquery.form.js"></script>
<script type="text/javascript">
    $().ready(function() {
        /*
         * DataTables
         */
        var votetable = $('#data-table').dataTable({
            "bServerSide": true,
            "bSortClasses": false,
            "sAjaxSource": url + "/ajax/vote",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                $.ajax( {
                    "dataType": 'json', 
                    "type": "POST", 
                    "url": sSource, 
                    "data": aoData, 
                    "success": fnCallback
                } );
            }
        });
        
        
        /*
         * Form Validation and Posting
         */
        var validateform = $("#vote").validate();
        $("#reset-form").click(function() {
            validateform.resetForm();
        });
        
        // ============================================
        // Create Vote Site
		$("#create").click(function() {
            // Show form, and hide any previous messages
            $('#vote-form').dialog({ modal: true, height: 420, width: 500 });
            $('#js_news_message').attr('style', 'display: none;');
            $('#vote').attr('style', '');
		});

        // ============================================
        // Delete Vote Site
        $("#data-table").delegate('.delete', 'click', function(){
            var vote_id = $(this).attr('name');
            
            if( confirm('Are you sure you want to delete vote site #' + vote_id) ){
                // Get our post
                $.post(url + "/ajax/vote", { action : 'delete', id : vote_id },
                    function(response){
                        // Parse the JSON response
                        var result = jQuery.parseJSON(response);
                        if (result.success == true)
                        {
                            // Display our Success message, and ReDraw the table so we imediatly see our action
                            $('#js_message').attr('class', 'alert success').html(result.message).slideDown(300).delay(3000).slideUp(600);
                            votetable.fnDraw();
                        }
                        else
                        {
                            $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                        }
                    }
                );
            }
        });
        
        // ===============================================
        // bind the News form using 'ajaxForm' 
        $('#vote').ajaxForm({
            beforeSubmit: function (arr, data, options){
                $('#vote').attr('style', 'display: none');
                $('#js_news_message').attr('class', 'alert loading').html('Submitting Form...').slideDown(300);
            },
            success: post_result,
            clearForm: true,
            timeout: 5000 
        });

        // Callback function for the News ajaxForm 
        function post_result(response, statusText, xhr, $form)  {
            // Parse the JSON response
            var result = jQuery.parseJSON(response);
            if (result.success == true){
                // Display our Success message, and ReDraw the table so we imediatly see our action
                $('#js_news_message').attr('class', 'alert success').html(result.message);
                votetable.fnDraw();
            }else{
                $('#js_news_message').attr('class', 'alert ' + result.type).html(result.message);
            }
        }
    })
</script>