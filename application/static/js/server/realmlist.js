/*
 * DataTables
 */
 $().ready(function() {
    // Bind the Realm table to Datatables
    $('#data-table').dataTable({
        "bServerSide": false,
        "bSortClasses": false,
        "bFilter": true,
        "bPaginate": false,
        "bJQueryUI": true
    });

});