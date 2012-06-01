$().ready(function() {
    var post_url = Plexis.url + "/admin_ajax/news";

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
    
    /** Create our vote form modal */
	var Modal = $("#news-modal").dialog({
		autoOpen: false,  
		modal: true, 
		width: 750,
        resizable: false,
		buttons: [{
			text: "Close", 
			click: function() {
				$( this ).dialog( "close" );
			}
		}]
	});

    /**
     * Init TinyMCE editor
     */
    $('textarea.tinymce').tinymce({
        // Location of TinyMCE script
        script_url : Plexis.template_url + '/js/tiny_mce/tiny_mce.js',

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
    });
    
    /**
     * Form Validation and Posting
     */
    $("#news-form").validate({
        rules: {
            title: {
                required: true,
                minlength: 3
            }
        }
    });
    
    /**
     * Bind the Create News button
     */
    $("#create").click(function() {
        // Open the Modal Window
		Modal.dialog("option", {
			title: "Add News Item"
		}).dialog("open");
        
        // Hide our close window button from view unless needed
        Modal.parent().find(".ui-dialog-buttonset").hide();
        
        // Reset the form
        $('#form-type').attr('value', 'create');
        $('#news-id').attr('value', '');
        $('#title').attr('value', '');
        $('textarea#body').val('');
        $('#submit').attr('value', 'Post');
        
        // Show the form
        $('#js_news_message').hide();
        $('#news-form').show();
    });
    
    /**
     * Edit News
     */
    $("#data-table").on('click', '.edit', function(){
        var news_id = this.name;
        
        // First we ajax to get the news information
        $.ajax({
            type: "POST",
            url: post_url,
            data: { action : 'get', id : news_id },
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
                if (result.success == true)
                {
                    // Less typing
                    result = result.message;
                    
                    // Set the form values
                    $('#form-type').attr('value', 'edit');
                    $('#news-id').attr('value', news_id);
                    $('#title').attr('value', result.title);
                    $('textarea#body').val( result.body );
                    $('#submit').attr('value', 'Update Post');
                    
                    // Make sure we hide the old message, and display the form
                    $('#js_news_message').hide();
                    $('#vote').show();
                    
                    // Open the Modal Window
                    Modal.dialog("option", {
                        title: "Edit News Post"
                    }).dialog("open");
                    
                    // Hide our close window button from view unless needed
                    Modal.parent().find(".ui-dialog-buttonset").hide();
                    
                    // Show the form
                    $('#js_news_message').hide();
                    $('#news-form').show();
                }
                else
                {
                    alert( result.message );
                }
            },
            error: function(request, status, err) 
            {
                $('#js_message').attr('class', 'alert error').html('Connection timed out. Please try again.').slideDown('slow').delay(3000).slideUp('slow');
            }
        });
    });

    /**
     * Delete News
     */
    $("#data-table").on('click', '.delete', function(){
        var news_id = this.name;
        
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
    
    /**
     * Bind the News form using 'ajaxForm' 
     */
    $('#news-form').ajaxForm({
        beforeSubmit: function (arr, data, options)
        {
            $('#js_news_message').attr('class', 'alert loading').html('Submitting Form...');
            $('#news-form').fadeOut(300, function(){
                $('#js_news_message').fadeIn(300);
            });
            return true;
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
        
        // Display our Success message, and ReDraw the table so we imediatly see our action
        $('#js_news_message').attr('class', 'alert ' + result.type).html(result.message);
        if (result.success == true)
        {
            newstable.fnDraw();
        }
        
        // Reshow the button to close the window!
        Modal.parent().find(".ui-dialog-buttonset").show();
    }
})