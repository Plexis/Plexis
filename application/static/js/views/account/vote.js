(function($) 
{
	$.extend(
    { 
		uiLock: function(content)
        {
			if(content == 'undefined') content = '';

			$('<div></div>').attr('id', 'uiLockId').css({
				'position': 'absolute',
				'top': 0,
				'left': 0,
				'z-index': 9999,
				'opacity': 0.6,
				'width':'100%',
				'height':'100%',
				'color':'white',
                'text-align':'center',
				'background-color':'black'
			}).html(content).appendTo('body');
		},
		uiUnlock: function()
        {
			$('#uiLockId').remove();
		}
	});
})(jQuery);


$().ready(function() 
{
    $("#vote").click(function() 
    {
        // Start by getting out ID out of the button name
        var id = $(this).attr('name');
        var timeout = 7000;

        // If the vote site is online, Lock the UI and open a new tab!
        $.uiLock('Locked until voting is complete, Please do not close or fresh this window.');
        childWindow = window.open( url + "/account/vote/out/" + id);
        setTimeout(function() 
        {
            $('#uiLockId').remove();
        
            // Check to make sure the vote window is still open
            if(childWindow && !childWindow.closed)
            {
                // submit the vote
                $.post(url + "/ajax/vote", { action : 'vote', site_id : id },
                    function(response)
                    {
                        // Parse the JSON response
                        var result = jQuery.parseJSON(response);
                        if (result.success == false)
                        {
                            // Display our Success message, and ReDraw the table so we imediatly see our action
                            alert('There was an error processing your vote. Please contact an administrator');
                        }
                        else
                        {
                            location.reload();
                        }
                    }
                );
            }
            else
            {
                alert('Vote window closed prematurely. Unable to verify vote status');
            }
        }, timeout);
    });
});