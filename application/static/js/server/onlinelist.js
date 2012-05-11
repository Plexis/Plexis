/*
 * DataTables
 */
 $().ready(function() {
    var realm_id = Plexis.realm_id;

    // Bind the Onlinelist table to Datatables
    var onlinelist = $('#online-table').dataTable({
        "bServerSide": true,
        "bSortClasses": false,
        "sPaginationType": "full_numbers",
        "bJQueryUI": true,
        "oLanguage": {
          "sZeroRecords": "There are no characters Online"
        },
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
    }).fnFilterOnReturn();
    
    // Set our current realm value
    $('#realm-changer').val( realm_id ).change();
    
    $('#realm-changer').change(function() {
        // Get our selected realm
        realm_id = $('#realm-changer').val();
        $('#realm-changer').val( realm_id ).change();
        
        // Load the character list view ajax
        onlinelist.fnReloadAjax( url + "/ajax/onlinelist/" + realm_id );
        $('#realm-name').html('<h3>' + $("#realm-changer option[value='" + realm_id + "']").text() + '</h3>');
    });
    
    // Set the name of the current realm of course ;)
    $('#realm-name').html('<h3>' + $("#realm-changer option[value='" + realm_id + "']").text() + '</h3>');

});