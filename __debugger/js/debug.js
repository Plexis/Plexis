// Globals variables 
var debugging = false;
var fetching = false;
var current_file = null;
var current_line = null;

/**
* Function: getVar
* Desc: Used to add a variable fetch request from the core debugger.
*/
function getVar(varname)
{
    updateStatus('Fetching Variable...');
    
    $.ajax({
        type: "POST",
        url: 'ajax.php',
        data: {action: 'get', data: varname},
        dataType: "json",
        timeout: 8000, // in milliseconds
        success: function(result) 
        {
            if(result.finished == true)
            {
                finished();
            }
            updateStatus(0);
        },
        error: function(request, status, err) 
        {
            show_ajax_error(status);
            updateStatus(0);
        }
    });
}

/**
* Function: setVar
* Desc: Used to add a variable set request to the core debugger.
*/
function setVar(varname, value, type)
{
    updateStatus('Setting Variable...');
    
    $.ajax({
        type: "POST",
        url: 'ajax.php',
        data: {action: 'set', variable: varname, value: value, type: type},
        dataType: "json",
        timeout: 8000, // in milliseconds
        success: function(result) 
        {
            if(result.finished == true)
            {
                finished();
            }
            updateStatus(0);
        },
        error: function(request, status, err) 
        {
            show_ajax_error(status);
            updateStatus(0);
        }
    });
}

/**
* Function: updateStatus
* Desc: Updates the status block on the GUI
*/
function updateStatus(msg) 
{
	if (msg == 0)
		var value = 'Idle';
	else 
		var value = msg;

	$('#debug_status').html(value);
}

/**
* Function: outputDisplay
* Desc: Updates the console screen with a line of text
*/
function outputDisplay(success, message) 
{
    if(!success)
        text = "<span class=\"c_error\">"+ message +"</span><br />";
    else
        text = "<span>"+ message +"</span><br />";
        
	$('#console').prepend( '<span class="c_prefix">$ </span> ' + text);
}

function focusOnLine(line)
{
    // Get size of code_window
    var code_window_height = parseInt($('#code_window').css('height'));
    var scroll_size = parseInt(code_window_height / 2);
    
    // Scroll to position in Code Editor
    var scroll_position = (parseInt(line) * 18);
    if (scroll_position > scroll_size) {
        // Scroll to the position of the line.
        scroll_position = scroll_position - scroll_size;
    } else {
        // Scroll to the top
        scroll_position = 0;	
    }
    $("#code_window").scrollTop(scroll_position);
}

/**
* Function: beginDebugging
* Desc: Initiates the debugging sequence
*/
function beginDebugging(url)
{
    // Reset all variables
    debugging = true;
    current_file = null;
    current_line = null;
    
    // Reset console
    $('#console').html('<span class="c_prefix">$ </span> <span class="c_arraykey">Starting debugging process...</span>');
    
    // Open chilc window and update status
    updateStatus('Initiating debugging process...');
    var ChildWindow = window.open(url + '&debug=1');
    
    // Create our ajax status loop
    window.timer = setInterval(function() 
    {
        // Prevent mutliple requests at the sime time on slower hosts
        if(fetching == false)
        {
            // Update our fetching var, and status
            fetching = true;
            updateStatus('Fetching update status...');
            
            $.ajax({
                type: "POST",
                url: 'ajax.php',
                data: {action: 'status'},
                dataType: "json",
                timeout: 8000, // in milliseconds
                success: function(result) 
                {
                    // Make sure the script is still running
                    if(result.finished == true)
                    { 
                        finished();
                    }
                    else
                    {
                        if(result.data.file != current_file || result.data.line != current_line)
                        {
                            updateStatus('Loading file...');
                            outputDisplay(true, '<span class="c_keyword">Debugging File: </span>' + result.data.file);
                            outputDisplay(true, '<span class="c_keyword">Debugging Line: </span>' + result.data.line);
                            
                            // Update file / line vars
                            current_file = result.data.file;
                            current_line = result.data.line;
                            
                            // Send the request to fetch the file
                            $.ajax({
                                type: "POST",
                                url: 'ajax.php',
                                data: {action: 'getFile'},
                                dataType: "json",
                                timeout: 8000, // in milliseconds
                                success: function(getFile) 
                                {
                                    if(result.finished == false)
                                    {
                                        $('#code_window').html(getFile.data);
                                        focusOnLine(result.data.line);
                                    }
                                    updateStatus(0);
                                },
                                error: function(request, status, err) 
                                {
                                    show_ajax_error(status);
                                    updateStatus(0);
                                }
                            });
                        }
                        
                        
                        if(result.data.output != null)
                        {
                            outputDisplay(true, result.data.output);
                        }
                    }
                },
                error: function(request, status, err) 
                {
                    show_ajax_error(status);
                    updateStatus(0);
                }
            });
            
            updateStatus(0);
            fetching = false;
        }
    }, 1000);
    
    
    $('#waiting').hide();
    $('#started').show();
    updateStatus(0);
}

/**
* Function: finished
* Desc: Completes the debugging sequence
*/
function finished()
{
    if(debugging == true)
    {
        debugging = false;
        clearInterval(window.timer);
        $('#code_window').html(null);
        outputDisplay(true, '<span class="c_keyword">Script has been terminated, or has completed its debugging cycle.</span>');
        $('#waiting').show();
        $('#started').hide();
        updateStatus('Debugging Finished');
    }
}


$().ready(function() {
    // Disable caching of AJAX responses
    $.ajaxSetup ({
    	cache: false
	});
    
    /**
     * Begin Button
     */
    $("a#begin").click( function() {
        $.msgbox('Please include the protocol (http://) in the URL. Also, if you are debugging the index page, be sure to add a trailing slash!<br />', {
            type : 'prompt',
            inputs : [
                {type: 'text', name: 'plexis_url', label: 'Plexis URL:', value:'', required : 'true'}
            ],
            buttons : [
                {type: 'cancel', value:'Cancel'},
                {type: 'submit', value:'Submit'}
            ]
        }, function(url) {
            if(url != false) {
                if(/^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i. test(url)) {
                    beginDebugging(url);
                } else {
                    alert("invalid url");
                }
            }
        });
    });
    
    /**
     * Kill Script Button
     */
    $("a#killScript").click( function() {
        updateStatus('Killing script...');

        $.ajax({
            type: "POST",
            url: 'ajax.php',
            data: {action: 'kill'},
            dataType: "json",
            timeout: 8000, // in milliseconds
            success: function(result) 
            {
                updateStatus(0);
            },
            error: function(request, status, err) 
            {
                show_ajax_error(status);
                updateStatus(0);
            }
        });
    });
    
    /**
     * Finish Script Button
     */
    $("a#finishScript").click( function() {
        $.ajax({
            type: "POST",
            url: 'ajax.php',
            data: {action: 'finish'},
            dataType: "json",
            timeout: 8000, // in milliseconds
            success: function(result) {},
            error: function(request, status, err) 
            {
                show_ajax_error(status);
                updateStatus(0);
            }
        });
    });
    
    /**
     * Get Variable Button
     */
    $("a#getVar").click( function() {
        $.msgbox('', {
            type : 'prompt',
            inputs : [
                {type: 'text', name: 'varname', label: 'Variable:', value:'', required : 'true'}
            ],
            buttons : [
                {type: 'cancel', value:'Cancel'},
                {type: 'submit', value:'Submit'}
            ]
        }, function(name) {
            if(name != false) {
                getVar(name);
            }
        });
    });
    
    /**
     * Modify Variable Button
     */
    $("a#modifyVar").click( function() {
        var ops = new Array();
        ops[0] = 'String';
        ops[1] = 'Integer';
        ops[2] = 'Bool';
        ops[3] = 'Float';
        ops[4] = 'Double';
        
        $.msgbox('', {
            type : 'prompt',
            inputs : [
                {type: 'text', name: 'varname', label: 'Variable Name:', value:'', required : 'true'},
                {type: 'select', label: 'Variable Type:', options: ops},
                {type: 'textarea', label: 'New Value', value:''}
            ],
            buttons : [
                {type: 'cancel', value:'Cancel'},
                {type: 'submit', value:'Submit'}
            ]
        }, function(name, val, type) {
            setVar(name, val, type);
        });
    });
    
    /**
     * Next Step Button
     */
    $("a#nextStep").click( function() {
        updateStatus('Processing Next Step...');
        outputDisplay(true, '<span class="c_arraykey">Processing Next Step...<br /></span>');
        
        $.ajax({
            type: "POST",
            url: 'ajax.php',
            data: {action: 'next'},
            dataType: "json",
            timeout: 8000, // in milliseconds
            success: function(result) {},
            error: function(request, status, err) 
            {
                show_ajax_error(status);
                updateStatus(0);
            }
        });
    });
});