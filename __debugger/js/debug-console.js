// Initialize global variables
var debug_running = false;

var current_pc = 0;
var current_db_num = 0;
var current_db_id = 0;

var current_script = 0;
var current_script_name = '';
var current_fq_script_name = '';

var fetch_request_count = 0;
var fetch_in_progress = false;
var fetch_time = 500;		// 500 Mili-seconds (1000 mili-seconds = 1 second)
var fetch_count_max = 5;	// 2.5 Seconds (if no response from the server then re-request data) change this is you want to see what speed works best with you.

var step_next = 0;

var default_status = 'None';

// When Document is ready fire everything up.
$(document).ready(function() {
	
	// This is mostly for Internet Explorer.
	$.ajaxSetup ({
	    // Disable caching of AJAX responses
    	cache: false
	});
	
	// Refresh current scripts.
	reload_scripts();	
	
	// Start Timer
	fetch_status();
	
});

function reload_scripts() {
	
	debug_status('Populating Pending Scripts');
	
	// Request scripts currently running from pending-scripts.php
  	$.getJSON("./ajax/pending-scripts.php", function(json) {
   	
		// Clear all scripts.
		$('#file_menu').html('');
		
   		var color = 'script_white';
		
   		// Request Script (Success) - Clear & Reset Running Scripts
		if(json != null)
		{
			$.each(json, function(i, value) 
			{
				// Define our script ID and color
				var script_id = '#script' + value.num;
				var key = i + 1;
				if (color == 'script_grey') {
					color = 'script_white';
				} else {
					color = 'script_grey';
				}
				
				// If a valid script then assign values. The ID's are arranged by the DB num not by the I		
				$('#file_menu').append(
				'<div id="script' + value.num +'" class="' + color + '">' +
					'<p class="left">' +
						'<a id="link_script' + value.num +'" onclick="debug_script(' + value.num + ', \'' + value.unique_id + '\', ' + key + '); return false;">' + value.name + '</a>' +
					'</p>' +
					'<p class="right">' + 
						'<a href="#" onclick="kill_script(' + value.num + ', \'' + value.unique_id + '\', 1); return false;">' + value.time + '</a>' +
					'</p>' +
				'</div>');
			
				// Highlight current File
				if (current_script == value.num) {
					// This script is currently selected and is in the code editor window.
					$(script_id).css('background-color', '#0000aa');
					$(script_id).css('color', '#ffffff');
				} else {
					// Script is not selected.
					$(script_id).css('background-color', '');
					$(script_id).css('color', '');
				}
			});
		}
		
		debug_status(0);
	});
	
}

function debug_script(db_num, db_id, line_id) {

	current_db_num = db_num;
	current_db_id = db_id;

	// Current db selected
	current_script = db_num;
	
	// Clear all open script selects/
	for (i=1;i<19;i++) {
		var script_id = '#script' + i;
		$(script_id).css('background-color', '');
		$(script_id).css('color', '');		
	}

	// Set new script to highlight
	$('#script' + db_num).css('background-color', '#0000aa');
	$('#script' + db_num).css('color', '#ffffff');
	
	
	// Load Editor with PHP file.
	load_editor_file();
	
	debug_running = true;
	
}

function fetch_status() {

	setInterval(function() {
	
		if (debug_running == true) {
		
			// This stops the script getting stuck, in case the server never sends a response.
			if (fetch_request_count == fetch_count_max) {
				fetch_request_count = 0;
				fetch_in_progress = false;
				// output_display('Request For Re-Send');
			}
			fetch_request_count++;
	
			if (fetch_in_progress == false) {
			
				fetch_in_progress = true;
	
				$.getJSON("./ajax/flags.php", { db_num: current_db_num, db_id: current_db_id }, function(json) {
   		
			   		// Get the data from the current debug script.
				   	var status = json.status;
				   	var pc = json.pc;
				   	var script_name = json.script_name;
				   	var fq_script_name = json.fq_script_name;
				   	var variable = json.variable;
				   	var variable_out = json.variable_out;
				   	
			   	
			   		// Status: 0 = (running), 1 = (handle lost), 2 = (application terminated), 3 = (breakpoints cleared)
				   	if (status == 1) {
				   		var msg = 'The current debug script has lost it\'s handle by another process, the debug process has been halted. However, the application may still running in the background';
				   		alert(msg);
				   		halt_debug();
				   		output_display(msg);
				   	}
				   	if (status == 2) {
				   		var msg = 'The current debug script has terminated/completed and is no longer running.';
				   		halt_debug();
				   		output_display(msg);
				   		reload_scripts();
				   	}
				   	if (status == 3) {
				   		var msg = 'The current debug script has cleared all its breakpoints.';
				   		halt_debug();
				   		output_display(msg);
				   		reload_scripts();
				   	}
				   	
				   	// Debugger has entered another file, load it and assign global values.
				   	if (current_fq_script_name != fq_script_name) {
				   		current_fq_script_name = fq_script_name;
				   		current_script_name = script_name;
				   		$('#link_script' + current_db_num).html(script_name);
				   		output_display('Loading file: ' + fq_script_name);
				   		load_editor_file();
				   	}
				   	
				   	// If a variable is available then push out to display.
				   	if (variable_out != '') {
				   		var variable_output = variable_out;
				   		output_display(variable_output);
				   	}
				   	
				   	// Clear existing (step point) & setup new (step point)
				   	focus_step_point(pc);
				   	
				   	// Reset Values
				   	if (step_next == 1) step_next = 0;
			   		
				   	// Allow script to run again.
				   	fetch_in_progress = false;
  			
				});
			
			}
	
		}
		
	}, fetch_time);

}

function focus_step_point(pc) {
	
	var old_line = '#bp' + current_pc;
	var new_line = '#bp' + pc;
	
	// In case new script has loaded (new line may not be visible)
	if ($(new_line)) {
		var line_background_color = $(new_line).css('background-color');
		
		// If not already set to line position.
		if (line_background_color != 'rgb(170, 0, 0)' && line_background_color.toUpperCase() != '#AA0000') {

			// Clear old Line position
			if ($(old_line)) {
				$(old_line).css('background-color', '');
				$(old_line).css('color', '');	
			}
			
			// Set new Line position
			$(new_line).css('background-color', '#AA0000');
			$(new_line).css('color', '#ffffff');
			
			// Get size of code_window
			var code_window_height = parseInt($('#code_window').css('height'));
			var scroll_size = parseInt(code_window_height / 2);
			
			// Scroll to position in Code Editor
			var scroll_position = (parseInt(pc) * 20);
			if (scroll_position > scroll_size) {
				// Scroll to the position of the line.
				scroll_position = scroll_position - scroll_size;
			} else {
				// Scroll to the top
				scroll_position = 0;	
			}
			$("#code_window").scrollTop(scroll_position);
			
			// Assign new Program Counter (PC) to current_pc
			current_pc = pc;

		}
	}
	
}

function load_editor_file() {
	// Fetch file contents, the load_file.php will get the filename from the database.
	$.ajax({
		url: './ajax/load_file.php',
		cache: false,
		data: {db_num : current_db_num, db_id: current_db_id},
		success: function(file_content){
			$('#code_window').html(file_content);		
		}
	});
}

function output_display(content) {
	$('#output_display').prepend('<p>' + content + '</p>');
}

function kill_script(db_num, db_id, type) {

	if (db_num == 0 || db_id == 0) {
		db_num = current_db_num;
		db_id = current_db_id;
	}
	
	debug_status('Kill Script');
	
	var extra_msg = "";
	
	// Kill from file_menu list
	if (type == '1') {
		extra_msg = "If you want to debug the script click on the filename.\r\nClicking on the time will terminate the script.\r\n\r\n";
	}
	
	// Kill ALL scripts from menu bar
	var kill_all = 0;
	if (type == '2') {
		extra_msg = "This will kill all current scripts in your file menu.\r\n\r\n";
		var kill_all = 1;
	}
	
	

	var answer = confirm(extra_msg + 'Are you sure you want to kill this script?');
	
	if (answer) {
		$.ajax({
			url: './ajax/kill_script.php',
			cache: false,
			data: {db_num : db_num, db_id: db_id, kill: 1, kill_everything: kill_all},
			success: function(content){
				// Reload Scripts
				reload_scripts();
			}
		});
	}
	
	debug_status(0);
}

function halt_debug() {
	debug_running = false;
}

function step_to_next() {
	$.ajax({
			url: './ajax/update_flags.php',
			cache: false,
			data: {db_num: current_db_num, db_id: current_db_id, step: 1},
			success: function(content){
				
			}
	});
}

function toggle_variable_popup() {
	var toggle = $('#popup_variable').css('display');
	
	if (toggle == 'block') {
		$('#popup_variable').css('display', 'none');
	} else {
		$('#popup_variable').css('display', 'block');
	}
}

function toggle_bo_popup() {
	var toggle = $('#popup_bo_variable').css('display');
	
	if (toggle == 'block') {
		$('#popup_bo_variable').css('display', 'none');
	} else {
		$('#popup_bo_variable').css('display', 'block');
	}
}

function popup_get_variable() {
	var variable_name = $('#variable_name').val();
	
	var format_variable_name = variable_name.replace(/"/g, '');
	// A bit strange but look above, it removes the single then below the double.
	var format_variable_name = format_variable_name.replace(/'/g, '');
	// Passes the formatted string back to the original variable.
	variable_name = format_variable_name;
	
	get_variable(variable_name, 1);
	$('#popup_variable').css('display', 'none');
}

function run_script() {
	

	debug_status('Clear BreakPoints');
	
	$.ajax({
			url: './ajax/update_flags.php',
			cache: false,
			data: {db_num : current_db_num, db_id: current_db_id, run: 1},
			success: function(content){
				debug_status(0);
			}
	});
	
}

function popup_set_variable() {
	
	var variable_name = $('#variable_name').val();
	var variable_value = $('#variable_value').val();
	
	debug_status('Setting: ' + variable_name);
	
	$.ajax({
			url: './ajax/update_flags.php',
			cache: false,
			data: {db_num : current_db_num, db_id: current_db_id, set_variable: variable_name, set_value: variable_value},
			success: function(content){
				debug_status(0);
			}
	});
	
	$('#popup_variable').css('display', 'none');
	
}

function popup_bo_variable(type) {
	
	var variable_name = $('#bo_variable_name').val();
	var variable_value = $('#bo_variable_value').val();
	
	if (type == 0) {
		// Clear break on
		variable_name = '';
		variable_value = '';
	}
	
	debug_status('Setting Break-On: ' + variable_name);
	
	$.ajax({
			url: './ajax/update_flags.php',
			cache: false,
			data: {db_num : current_db_num, db_id: current_db_id, bo_set_variable: variable_name, set_value: variable_value},
			success: function(content){
				debug_status(0);
			}
	});
	
	$('#popup_bo_variable').css('display', 'none');
	
}

function get_variable(variable_name, add) {
	debug_status('Requesting: ' + variable_name);

	// If request came from code_window add to quick lanuch variable.
	if (add == 1) update_quick_variables(variable_name);
	
	$.ajax({
			url: './ajax/update_flags.php',
			cache: false,
			data: {db_num : current_db_num, db_id: current_db_id, get_variable: variable_name},
			success: function(content){
				// Scroll to the top of the output window - Only for variables
				$("#output_display").scrollTop(0);
				debug_status(0);
			}
	});
}

function update_quick_variables(variable_name) {
	var format_variable_name = variable_name.replace(/"/g, '');
	// A bit strange but look above, it removes the single then below the double.
	var format_variable_name = format_variable_name.replace(/'/g, '');
	// Passes the formatted string back to the original variable.
	variable_name = format_variable_name;
	$('#current_variables').prepend('<p><a href="#" class="r" onclick="get_variable(\'' + variable_name + '\', 0); return false;">' + variable_name + '</a></p>');
}

function debug_status(msg) {
	if (msg == 0) {
		var value = default_status;
	} else {
		var value = msg;
	}
	$('#debug_status').html(value);
}

function load_page() {
	
	var url_address = $('#url_address').val();
	
	var new_code_window = prompt("Enter your URL that you want to load then click 'Reload Scripts': ", url_address);

	if (new_code_window != null && new_code_window != "") {
  		window.open(new_code_window);
  	}
}

