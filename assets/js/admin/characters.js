/*
 * DataTables
 */
$().ready(function() {
    // Get the current realm id
    var realm_id = $('#realm-changer').val();
    
    // Bind the Onlinelist table to Datatables
    var table = $('#character-table').dataTable({
        "bServerSide": true,
        "bSortClasses": false,
        "sAjaxSource": Plexis.url + "/admin_ajax/characters/" + Plexis.realm_id,
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            $.ajax( {
                "dataType": 'json', 
                "type": "POST", 
                "url": sSource, 
                "data": aoData, 
                "success": function (result, status, jqXHR) {
                    if(typeof result.php_error != "undefined" && result.php_error == true)
                    {
                        show_php_error(result.php_error_data);
                    }
                    else
                    {
                        fnCallback(result, status, jqXHR);
                    }
                },
                "error": function(request, status, err) {
                    show_ajax_error(status);
                }
            } );
        }
    });
    
    $('#realm-changer').change(function() {
        // Get the selected realm, and make sure the select updates
        realm_id = $('#realm-changer').val();
        $('#realm-changer').val( realm_id );
        
        // Reload the characters table with the new realm id
        table.fnReloadAjax( Plexis.url + "/admin_ajax/characters/" + realm_id );
    });
});