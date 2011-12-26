<!-- Button -->
<div>
    <a id="create" href="javascript:void(0);" class="button">Create New Post</a>
</div>

<!-- News List Table -->
<div class="block-border">
    <div class="block-header">
        <h1>News Posts</h1><span></span>
    </div>
    <div class="block-content">
        <div id="js_message" style="display: none;"></div>
        <table id="data-table" class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Posted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Hidden Create / Edit form -->
<div id="news-form" title="Create News Post" style="display: none;">
    <div id="js_news_message" style="display: none;"></div>
    <form id="news" class="form" action="{SITE_URL}/ajax/news" method="post">
        <input type="hidden" name="action" value="create">
        <div class="_100">
            <p>
                <label for="title">News Title</label>
                <input id="title" name="title" class="required" type="text" value="" />
            </p>
        
            <div>
                <label for="body">News Body</label>
                <textarea id="body" name="body" class="tinymce"></textarea>
            </div>

            <div>
                <input id="submit" type="submit" class="button" style="width: 150px; text-align: center; margin: 10px; float: right;" value="Submit">
            </div>
        </div>
    </form>
</div>

<!-- DataTables for Ajax loading -->
<script src="{TEMPLATE_URL}/js/mylibs/jquery.form.js"></script>
<script type="text/javascript">
    $().ready(function() {
        // Define our URL here
        var url = '{SITE_URL}';
        /*
         * DataTables
         */
        var newstable = $('#data-table').dataTable({
            "bServerSide": true,
            "bSortClasses": false,
            "sAjaxSource": url + "/ajax/news",
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
        var validateform = $("#news").validate();
        $("#reset-form").click(function() {
            validateform.resetForm();
        });
        
        // ============================================
        // Create News
		$("#create").click(function() {
            // Show form, and hide any previous messages
            $('#news-form').dialog({ modal: true, height: 550, width: 750 });
            $('#js_news_message').attr('style', 'display: none;');
            $('#news').attr('style', '');
            
            // Init TinyMCE editor
            $('textarea.tinymce').tinymce({
                // Location of TinyMCE script
                script_url : '{TEMPLATE_URL}/js/tiny_mce/tiny_mce.js',

                // General options
                theme : "advanced",
                plugins : "pagebreak,style,layer,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

                // Theme options
                theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
                theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                theme_advanced_resizing : true,

                // Example content CSS (should be your site CSS)
                content_css : "",

                // Drop lists for link/image/media/template dialogs
                template_external_list_url : "lists/template_list.js",
                external_link_list_url : "lists/link_list.js",
                external_image_list_url : "lists/image_list.js",
                media_external_list_url : "lists/media_list.js",

                // Replace values for the template plugin
                template_replace_values : {
                    username : "Some User",
                    staffid : "991234"
                }
            });
		});

        // ============================================
        // Delete News
        $("#data-table").delegate('.delete', 'click', function(){
            var news_id = $(this).attr('name');
            
            if( confirm('Are you sure you want to delete news post #' + news_id) ){
                // Get our post
                $.post(url + "/ajax/news", { action : 'delete', id : news_id },
                    function(response){
                        // Parse the JSON response
                        var result = jQuery.parseJSON(response);
                        if (result.success == true)
                        {
                            // Display our Success message, and ReDraw the table so we imediatly see our action
                            $('#js_message').attr('class', 'alert success').html(result.message).slideDown(300).delay(3000).slideUp(600);
                            newstable.fnDraw();
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
        $('#news').ajaxForm({
            beforeSubmit: function (arr, data, options){
                $('#news').attr('style', 'display: none');
                $('#js_news_message').attr('class', 'alert loading').html('Submitting Form...').slideDown(300);
            },
            success: news_result,
            clearForm: true,
            timeout: 5000 
        });

        // Callback function for the News ajaxForm 
        function news_result(response, statusText, xhr, $form)  {
            // Parse the JSON response
            var result = jQuery.parseJSON(response);
            if (result.success == true){
                // Display our Success message, and ReDraw the table so we imediatly see our action
                $('#js_news_message').attr('class', 'alert success').html(result.message);
                newstable.fnDraw();
            }else{
                $('#js_news_message').attr('class', 'alert ' + result.type).html(result.message);
            }
        }
    })
</script>