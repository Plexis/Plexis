<div class="grid_12">

    <!-- RealmList Table -->
    <div class="block-border">
        <div class="block-header">
            <h1>Realmlist</h1><span></span>
        </div>
        <div class="block-content">
            <div id="js_message" style="display: none;"></div>
            <table id="realm-table" class="table">
                <thead>
                    <tr>
                        <th>Realm ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Port</th>
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
        // Define our URL here
        var url = '{SITE_URL}';
        
        /*
         * DataTables
         */
        var realmtable = $('#realm-table').dataTable({
            "bServerSide": true,
            "bSortClasses": false,
            "sAjaxSource": url + "/ajax/realms",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                aoData.push( { "name": "action", "value": "admin" } );
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
        // Uninstall Realm
        $("#realm-table").delegate('.un-install', 'click', function(){
            var r_id = $(this).attr('name');
            
            if( confirm('Are you sure you want to uninstall realm #' + r_id) ){
                // Get our post
                $.post("{SITE_URL}/ajax/realms", { action : 'un-install', id : r_id },
                    function(response){
                        // Parse the JSON response
                        var result = jQuery.parseJSON(response);
                        if (result.success == true)
                        {
                            // Display our Success message, and ReDraw the table so we imediatly see our action
                            $('#js_message').attr('class', 'alert success').html(result.message).slideDown(300).delay(3000).slideUp(600);
                            realmtable.fnDraw();
                        }
                        else
                        {
                            $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                        }
                    }
                );
            }
        });
        
        // ============================================
        // Make Realm Default
        $("#realm-table").delegate('.make-default', 'click', function(){
            var r_id = $(this).attr('name');
            
            // Get our post
            $.post("{SITE_URL}/ajax/realms", { action : 'make-default', id : r_id },
                function(response){
                    // Parse the JSON response
                    var result = jQuery.parseJSON(response);
                    if (result.success == true)
                    {
                        // Display our Success message, and ReDraw the table so we imediatly see our action
                        $('#js_message').attr('class', 'alert success').html(result.message).slideDown(300).delay(3000).slideUp(600);
                        realmtable.fnDraw();
                    }
                    else
                    {
                        $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                    }
                }
            );
        });
    });
</script>