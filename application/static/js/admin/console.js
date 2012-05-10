/**
 * Code list
 *
 * 100: Server Error / Unknown Action
 * 200: OK
 * 300: Error Authenticating or realm offline
 * 400: Command problem or invalid command
 */
// Variables
var command_prefix = '<span class="c_prefix">$</span> ';
var commands_array = Array();
var command_position = 0;
var post_url = Plexis.url + "/admin_ajax/console";
var user = '';
var pass = '';
var connection = '';

// Send our Init. command on page load
$(document).ready(function() {
    init_window();
    
    // Process all changes to the realm selector
    $('#realm').change(function() {
        // get our selected realm id
        realm = $('#realm').val();
        text = " <span class='c_keyword'>Setting realm: " + $("#realm option[value='" + realm + "']").text() + "</span>";
        $("#console").html( $("#console").html() + "<br />" + text + "<br />");
    });
});


// Clears the console and loads the default welcome message
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
            // Clear the console and present the welcome message
            $("#console").html( result.show );
        
            // Get our realm ID
            var realm = $('#realm').val();
            if(realm == 0)
            {
                // Update the console Window
                var text = "No Realms Installed. You will need to install at least 1 realm before being able to send server commands"
                    + "<br />You may also create a custom connection by going \"connect #host #port #type (Telnet or Soap)\" <br />";
                $("#console").html( $("#console").html() + "<br /><span class=\"c_keyword\">" + text + "</span>" );
            }
            else
            {
                // Allow commands ;)
                var text = " <span class=\"c_keyword\">Selected realm: " + $("#realm option[value='" + realm + "']").text() + "</span>";
                $("#console").html( $("#console").html() + "<br />" + text + "<br />");
            }
            $("#command").attr('disabled', false).focus();
        },
        error: function(request, status, err) 
        {
            // Update the console Window
            text = "<br /><span class=\"c_error\">There was an error loading the command window</span><br />";
            $("#console").html( $("#console").html() + text);
        }
    });
}

// Execute the command on a key press event
function execute(field, event) 
{
    // Get the keycode
    var key_code = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
    
    // Arrow Up Key
    if(key_code == 38)
    {
        // Go up one command, and update the command input value
        if(command_position > 0) command_position--;
        $("#command").val(commands_array[command_position]);
    }
    
    // Arrow Down Key
    else if(key_code == 40)
    {
        // Go down one command, and update the command input value
        if(command_position < (commands_array.length-1)) command_position++;
        $("#command").val(commands_array[command_position]);
    }
    
    // User presses Enter
    if (key_code == 13)
    {
        // Get the inputed command and Realm
        var command = $("#command").val();
        realm = $('#realm').val();
        
        // return if there is no realms installed and no custom connect
        if( command == '' || (realm == 0 && command.indexOf("connect") == -1 && connection == ''))
        {
            return;
        }

        // Reset the command input value
        $("#command").val('');

        // Add command to the commands stack and add the new position of this command
        commands_array.push( command );
        command_position = (commands_array.length);
        
        // Check our command for "disconnect" first
        if(command == "disconnect")
        {
            // Show error if there is no connection!
            if(connection == '')
            {
                text = "<br />" + command_prefix + "<span class=\"c_keyword\">disconnect</span><br />";
                text += " <span class=\"c_error\">You must establish a connection before disconnecting.</span><br />";
                $("#console").html( $("#console").html() + text);
                $("#command").focus();
            }
            else
            {
                // Reset the login and connection variables
                connection = '';
                user = '';
                pass = '';
                
                // Update the console window
                text = "<br />" + command_prefix + "<span class=\"c_keyword\">" + command + 
                    "</span><br /> Success. You have also been logged out.<br />";
                $("#console").html( $("#console").html() + text);
                $("#command").focus();
            }
            scroll();
            return;
        }
        
        // Next connect
        else if(command.indexOf("connect") != -1)
        {
            // Remove the connect prefix
            args = command.split(' ');
            args.shift()
            command = args.join(' ');
            
            // Make sure we have all arguments
            if( 0 in args && 1 in args && 2 in args )
            {
                // Reset login information and set connection variable
                connection = command;
                user = '';
                pass = '';
                
                // Update the window
                text = "<br />" + command_prefix + "<span class=\"c_keyword\">connect</span> " + command + 
                    "<br /> Please Login...<br />";
                $("#console").html( $("#console").html() + text);
                $("#command").focus();
            }
            else
            {
                text = "<br />" + command_prefix + "<span class=\"c_keyword\">connect</span> " + command + "<br />";
                text += " <span class=\"c_error\">Syntax error: Improper connection string format.</span><br />";
                $("#console").html( $("#console").html() + text);
                $("#command").focus();
            }
            scroll();
            return;
        }
        
        // Check our command for a login command
        else if(command.indexOf("login") != -1)
        {
            // Remove the login prefix
            args = command.split(' ');
            args.shift();
            
            // Make sure we have all arguments
            if( 0 in args && 1 in args )
            {
                user = args[0];
                pass = args[1];
            }
            else
            {
                command = args.join(' ');
                text = "<br />" + command_prefix + "<span class=\"c_keyword\">login</span> " + command + "<br />";
                text += " <span class=\"c_error\">Syntax error: Improper login string format.</span><br />";
                $("#console").html( $("#console").html() + text);
                $("#command").focus();
                scroll();
                return;
            }
        }
        
        // Check our command for a logout command
        else if(command == "logout")
        {
            user = '';
            pass = '';
            text = "<span class=\"c_keyword\">logout</span><br />Logged out successfully.<br />";
            $("#console").html( $("#console").html() + "<br />" + command_prefix + text);
            $("#command").focus();
            scroll();
            return;
        }

        // Check our command for "clear"
        else if(command == "clear")
        {
            init_window();
            $("#command").focus();
            return;
        }

        // Make sure we are logged in ^^
        else if((user == '' || pass == '') && connection == '')
        {
            text = "<br /><span class=\"c_error\">You must login into the remote server first! \"login username password\"</span><br />";
            $("#console").html( $("#console").html() + text);
            $("#command").focus();
            scroll();
            return;
        }
        
        // Add our command to the window
        highlighted = highlight_command( command );
        $("#console").html( $("#console").html() + "<br />" + command_prefix +  highlighted + "<br />");
        $("#command").focus();
        scroll();
        
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
                        show = result.show;
                        break;

                    default:
                        show = '<span class="c_error">' + result.show + "</span>";
                        break;
                }
                
                // Update the console Window
                $("#console").html( $("#console").html()  + show + "<br />").focus();
                
                // Keep to the bottom of the frame
                scroll();
                
                // Set focus to command input field
                $("#command").focus();
            },
            error: function(request, status, err) 
            {
                // Update the console Window
                $("#console").html( $("#console").html() + "<span class=\"c_error\">Connection Timed out</span><br />");
                $("#command").focus();
            }
        });
        return false;
    }
    else
    {
        return true;
    }
}

// Nifty method of highlighting key command words
function highlight_command( command )
{
    // Trim and conver command into an array
    command = $.trim(command);
    command = command.split(' ');
    
    switch(command[0])
    {
        case "login":
            // Login is special, we have to hide the password
            command[0] = "<span class=\"c_keyword\">" + command[0] + "</span>";
            chars = command[2].split();
            length = command[2].length;
            command[2] = '';
            
            // Loop through each character in the pass and replace with "*"
            for (var i=0; i < length; i++) 
            {
                command[2] += '*';
            }  
            string = command.join(' ');
            break;
            
        case "send":
        case "character":  
        case "server":   
        case "ticket":
        case "unban":
        case "ban":
        case "banlist":
        case "guild":
        case "list":
        case "lookup":
        case "reset":
        case "account":
        case "reload":
            // Process commands that have 2 - 3 parts
            command[0] = "<span class=\"c_keyword\">" + command[0] + "</span>";
            if(1 in command)
            {
                if(command[1] == 'set' && 2 in command)
                {
                    command[2] = "<span class=\"c_keyword\">" + command[2] + "</span>";
                }
                command[1] = "<span class=\"c_keyword\">" + command[1] + "</span>";
            }
            string = command.join(' ');
            break;
            
        default:
            // Just 1 part commands
            command[0] = "<span class=\"c_keyword\">" + command[0] + "</span>";
            string = command.join(' ');
    }
    return string;
}

// Used to keep the command window at the bottom
function scroll()
{
    div = document.getElementById('console');
    div.scrollTop = div.scrollHeight;
}