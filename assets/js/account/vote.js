$().ready(function() 
{
    var timeout = 7000;
    var vote_window_html = $("#vote-window").html();
    
    // Create our base loading modal
	Modal = $("#vote-window").dialog({
		autoOpen: false, 
		title: "Waiting for Vote Verification", 
		modal: true, 
		width: "500",
		buttons: [{
			text: "Close Window", 
			click: function() {
				$( this ).dialog( "close" );
			}
		}]
	});
    
    $("#vote").click(function() 
    {
        // Start by getting out ID out of the button name
        var id = this.name;
        
        // Open the Modal Window
		Modal.dialog("option", {
			modal: true, 
			open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
			closeOnEscape: false, 
			draggable: false,
			resizable: false
		}).dialog("open");
        
        // Hide our close window button from view unless needed
        Modal.parent().find(".ui-dialog-buttonset").hide();
        
        // Reset our vote-window div html
        $("#vote-window").html( vote_window_html );

        // If the vote site is online, Lock the UI and open a new tab!
        childWindow = window.open( Plexis.url + "/account/vote/out/" + id);
        setTimeout(function() 
        {
            // Check to make sure the vote window is still open
            if(childWindow && !childWindow.closed)
            {
                // submit the vote
                $.post( Plexis.url + "/ajax/vote", { action : 'vote', site_id : id },
                    function(response)
                    {
                        // Parse the JSON response
                        var result = jQuery.parseJSON(response);
                        if (result.success == false)
                        {
                            // Display our Success message, and ReDraw the table so we imediatly see our action
                            $("#vote-window").html('<p><div class="alert error">There was an error processing your vote. Please contact an administrator</div></p>');
                            Modal.parent().find(".ui-dialog-buttonset").show();
                        }
                        else
                        {
                            Modal.dialog('close');
                            location.reload();
                        }
                    }
                );
            }
            else
            {
                $("#vote-window").html('<p><div class="alert error">Vote window closed prematurely. Unable to verify vote status</div></p>');
                Modal.parent().find(".ui-dialog-buttonset").show();
            }
        }, timeout);
    });
});