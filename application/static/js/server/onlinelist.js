/*
 * DataTables
 */
 $().ready(function() {
    var realm_id = Plexis.realm_id;

    // Bind the Onlinelist table to Datatables
    var onlinelist = $('#online-table').dataTable({
        "bServerSide": true,
        "bSortClasses": false,
        "sAjaxSource": Plexis.url + "/ajax/onlinelist/" + Plexis.realm_id,
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
    
    $('#realm-changer').change(function() {
        // select realm
        realm_id = $('#realm-changer').val();
        onlinelist.fnReloadAjax( url + "/ajax/onlinelist/" + realm_id );
        $('#realm-name').html('<h3>' + $("#realm-changer option[value='" + realm_id + "']").text() + '</h3>');

    });
    
    // Set the name of the current realm of course ;)
    $('#realm-name').html('<h3>' + $("#realm-changer option[value='" + realm_id + "']").text() + '</h3>');

});