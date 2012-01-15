/**
 * Code list
 *
 * 100: Server Error / Unknown Action
 * 200: OK
 * 300: Error Authenticating or realm offline
 * 400: Command problem or invalid command
 */
// Variables
var command_prefix = '<span class="c_prefix">$</span>';
var commands_array = Array();
var command_position = 0;
var post_url = url + "/ajax/console";
var user = '';
var pass = '';
var connection = '';

$(document).ready(function() {
    // Send our Init. command
    init_window()
});

$('#realm').change(function() {
    // select realm
    realm = $('#realm').val();
    text = " <span class='c_keyword'>Setting realm: " + $("#realm option[value='" + realm + "']").text() + "</span>";
    $("#console").html( $("#console").html() + "<br />" + text + "<br />");
});

function init_window()
{
    // Send our Init. command
    $.ajax({
        type: "POST",
        url: post_url,
        data: {action: 'init'},
        dataType: "json",
        timeout: 5000, // in milliseconds
        success: function(result) 
        {
            $("#console").html( result.show );
        
            // Get our realm ID
            var realm = $('#realm').val();
            if(realm == 0)
            {
                // Update the console Window
                var text = "No Realms Installed. You will need to install at least 1 realm before being able to send server commands";
                $("#console").html( $("#console").html() + "<br /><span class='c_keyword'>" + text + "</span>" );
                return;
            }
            else
            {
                // Allow commands ;)
                var text = " <span class='c_keyword'>Selected realm: " + $("#realm option[value='" + realm + "']").text() + "</span>";
                $("#console").html( $("#console").html() + "<br />" + text + "<br />");
                $("#command").attr('disabled', false);
                $("#command").focus();
            }
        },
        error: function(request, status, err) 
        {
            // Update the console Window
            $("#console").html( $("#console").html() + "<br /><span class=\"c_error\">There was an error loading the command window</span><br />").focus();
        }
    });
}

function execute(field, event) 
{
    // Get the keycode
    var theCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
    
    // Arrow Up
    if(theCode == 38)
    {
        if(command_position > 0) command_position--;
        $("#command").val(commands_array[command_position]);
    }
    
    // Arrow Down
    else if(theCode == 40)
    {
        if(command_position < (commands_array.length-1)) command_position++;
        $("#command").val(commands_array[command_position]);
    }
    
    // Enter
    else if (theCode == 13)
    {
        // Get the inputed command and Realm
        var command = $("#command").val();
        realm = $('#realm').val();
        
        // return if there is no realms installed and no custom connect
        if( realm == 0 && command.indexOf("connect") == -1 )
        {
            return;
        }

        // Reset the command value
        $("#command").val('');

        // Add command to stack and update position
        commands_array.push( command );
        command_position = (commands_array.length);
        
        // Check our command for "disconnect" first
        if(command == "disconnect")
        {
            // Show error if there is no connection!
            if(connection == '')
            {
                $("#console").html( $("#console").html() + "<br />" + command_prefix + " <span class=\"c_keyword\">disconnect</span><br />");
                $("#console").html( $("#console").html() + " <span class=\"c_error\">You must establish a connection before disconnecting.</span><br />");
                scroll();
                return;
            }
            else
            {
                connection = '';
                user = '';
                pass = '';
                $("#console").html( $("#console").html() + "<br />" + command_prefix + " <span class=\"c_keyword\">" + command + "</span><br />");
                $("#console").html( $("#console").html() + " Success. You have also been logged out.<br />");
                scroll();
                return;
            }
        }
        
        // Next connect
        else if(command.indexOf("connect") != -1)
        {
            // Turn to array
            args = command.split(' ');
            
            // Make sure we have all arguments
            if( 1 in args && 2 in args && 3 in args )
            {
                args.shift()
                command = args.join(' ');
                connection = command;
                $("#console").html( $("#console").html() + "<br />" + command_prefix + " <span class=\"c_keyword\">connect</span> " + command + "<br />");
                $("#console").html( $("#console").html() + " Success. Please Login...<br />");
                scroll();
            }
            else
            {
                args.shift()
                command = args.join(' ');
                $("#console").html( $("#console").html() + "<br />" + command_prefix + " <span class=\"c_keyword\">connect</span> " + command + "<br />");
                $("#console").html( $("#console").html() + " <span class=\"c_error\">Syntax error: Improper connection string format.</span><br />");
                scroll();
            }
            return;
        }
        
        // Check our command for a login command
        else if(command.indexOf("login") != -1)
        {
            args = command.split(' ');
            
            // Make sure we have all arguments
            if( 1 in args && 2 in args )
            {
                args.shift()
                user = args[0];
                pass = args[1];
            }
            else
            {
                args.shift()
                command = args.join(' ');
                $("#console").html( $("#console").html() + "<br />" + command_prefix + " <span class=\"c_keyword\">login</span> " + command + "<br />");
                $("#console").html( $("#console").html() + " <span class=\"c_error\">Syntax error: Improper login string format.</span><br />");
                scroll();
                return;
            }
        }
        
        // Check our command for a logout command
        else if(command.indexOf("logout") != -1)
        {
            user = '';
            pass = '';
            $("#console").html( $("#console").html() + "<br /><span class=\"c_keyword\"> Logged Out Successfully</span><br />");
            scroll();
            return;
        }

        // Check our command for "clear"
        else if(command == "clear")
        {
            init_window();
            
            // Reset our commands
            commands_array = Array();
            command_position = 0;
            return;
        }

        // Make sure we are logged in ^^
        else if(user == '' || pass == '')
        {
            $("#console").html( $("#console").html() + "<br /><span class=\"c_error\">You must login into the remote server first! \"login username password\"</span><br />");
            scroll();
            return;
        }
        
        // Send our command
        $.ajax({
            type: "POST",
            url: post_url,
            data: {action: 'command', command: command, realm: realm, user: user, pass: pass, overide: connection},
            dataType: "json",
            timeout: 5000, // in milliseconds
            success: function(result) 
            {
                switch(result.status)
                {
                    case 200:
                        com = (result.command == null) ? "" : command_prefix + ' ' + result.command + "<br />";
                        show = (result.show == null) ? "Success <br />" : result.show + "<br />";
                        break;
                        
                    case 300:
                        com = (result.command == null) ? "" : command_prefix + ' ' + result.command + "<br />";
                        show = result.show + "<br />";
                        break;
                        
                    case 400:
                        com = (result.command == null) ? "" : command_prefix + ' ' + result.command + "<br />";
                        show = (result.show == null) ? "<span class=\"c_error\">Invalid Command</span><br />" : "<span class=\"c_error\">" + result.show + "</span><br />";
                        break;
                        
                    default:
                        com = (result.command == null) ? "" : command_prefix + ' ' + result.command + "<br />";
                        show = '<span class="c_error">' + result.show + "</span><br />";
                        break;
                }
                
                // Update the console Window
                $("#console").html( $("#console").html() + "<br />" + com  + show ).focus();
                
                // Keep to the bottom of the frame
                scroll();
                
                // Set focus to command input field
                $("#command").focus();
            },
            error: function(request, status, err) 
            {
                // Update the console Window
                $("#console").html( $("#console").html() + "<br /><span class=\"c_error\">Connection Timed out</span><br />").focus();
            }
        });
        return false;
    }
    else
    {
        return true;
    }
}

function scroll()
{
    // Keep to the bottom of the frame
    div = document.getElementById('console');
    div.scrollTop = div.scrollHeight;
}