$().ready(function() {
    var post_url = url + "/ajax/news";

    /**
     * DataTables
     */
    var newstable = $('#data-table').dataTable({
        "bServerSide": true,
        "bSortClasses": false,
        "sAjaxSource": post_url,
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
            script_url : template_url + '/js/tiny_mce/tiny_mce.js',

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
        
        if( confirm('Are you sure you want to delete news post #' + news_id) )
        {
            // Send our delete command
            $.ajax({
                type: "POST",
                url: post_url,
                data: { action : 'delete', id : news_id },
                dataType: "json",
                timeout: 5000, // in milliseconds
                success: function(result) 
                {
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
                },
                error: function(request, status, err) 
                {
                    $('#js_message').attr('class', 'alert error').html('Connection timed out. Please try again.').slideDown(300).delay(3000).slideUp(600);
                }
            });
        }
    });
    
    // ===============================================
    // bind the News form using 'ajaxForm' 
    $('#news').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#news').attr('style', 'display: none');
            $('#js_news_message').attr('class', 'alert loading').html('Submitting Form...').slideDown(300);
        },
        success: news_result,
        clearForm: true,
        timeout: 5000 
    });

    // Callback function for the News ajaxForm 
    function news_result(response, statusText, xhr, $form)  
    {
        // Parse the JSON response
        var result = jQuery.parseJSON(response);
        if (result.success == true)
        {
            // Display our Success message, and ReDraw the table so we imediatly see our action
            $('#js_news_message').attr('class', 'alert success').html(result.message);
            newstable.fnDraw();
        }
        else
        {
            $('#js_news_message').attr('class', 'alert ' + result.type).html(result.message);
        }
    }
})