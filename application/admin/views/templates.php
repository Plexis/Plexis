<div class="grid_12">

    <!-- Templates Table -->
    <div class="block-border">
        <div class="block-header">
            <h1>Site Templates</h1><span></span>
        </div>
        <div class="block-content">
            <div id="js_message" style="display: none;"></div>
            <table id="templates-table" class="table">
                <thead>
                    <tr>
                        <th>Template Name</th>
                        <th>Type</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $().ready(function() {
        var post_url = url + "/ajax/templates";
        
        /*
         * DataTables
         */
        var table = $('#templates-table').dataTable({
            "bServerSide": true,
            "bSortClasses": false,
            "sAjaxSource": post_url,
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                aoData.push( { "name": "action", "value": "getlist" } );
                $.ajax( {
                    "dataType": 'json', 
                    "type": "POST", 
                    "url": sSource, 
                    "data": aoData, 
                    "success": fnCallback
                } );
            }
        });
        
        // ============================================
        // Install
        $("#templates-table").delegate('.install', 'click', function(){
            var name = $(this).attr('name');
            
            // Send our Install command
            $.ajax({
                type: "POST",
                url: post_url,
                data: {action: 'install', id: name},
                dataType: "json",
                timeout: 5000, // in milliseconds
                success: function(result) 
                {
                    if (result.success == true)
                    {
                        // Display our Success message, and ReDraw the table so we imediatly see our action
                        $('#js_message').attr('class', 'alert success').html(result.message).slideDown(300).delay(3000).slideUp(600);
                        table.fnDraw();
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
        });
        
        // ============================================
        // Uninstall
        $("#templates-table").delegate('.un-install', 'click', function(){
            var name = $(this).attr('name');

            // Send our Uninstall command
            $.ajax({
                type: "POST",
                url: post_url,
                data: {action: 'un-install', id: name},
                dataType: "json",
                timeout: 5000, // in milliseconds
                success: function(result) 
                {
                    if (result.success == true)
                    {
                        // Display our Success message, and ReDraw the table so we imediatly see our action
                        $('#js_message').attr('class', 'alert success').html(result.message).slideDown(300).delay(3000).slideUp(600);
                        table.fnDraw();
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
        });
        
        // ============================================
        // Make Default
        $("#templates-table").delegate('.make-default', 'click', function(){
            var name = $(this).attr('name');
            
            // Send our Install command
            $.ajax({
                type: "POST",
                url: post_url,
                data: {action: 'make-default', id: name},
                dataType: "json",
                timeout: 5000, // in milliseconds
                success: function(result) 
                {
                    if (result.success == true)
                    {
                        // Display our Success message, and ReDraw the table so we imediatly see our action
                        $('#js_message').attr('class', 'alert success').html(result.message).slideDown(300).delay(3000).slideUp(600);
                        table.fnDraw();
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
        });
    });
</script>