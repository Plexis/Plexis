/*
 * DataTables
 */
 $().ready(function() {
    var onlinelist = $('#online-table').dataTable({
        "bServerSide": true,
        "bSortClasses": false,
        "sAjaxSource": url + "/ajax/onlinelist",
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
});