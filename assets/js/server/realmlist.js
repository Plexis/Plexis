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

    // Send our Init. command
    $.ajax({
        type: "POST",
        url: Plexis.url + '/ajax/realms',
        data: {action: 'status'},
        dataType: "json",
        timeout: 5000, // in milliseconds
        success: function(result) 
        {
            _parse_status(result);
        },
        error: function(request, status, err) 
        {
            // do nothing
        }
    });
    
    // Parses the div html and replacing the template vars
    function _parse_status(result)
    {
        var count = result.length;
        for (i = 0; count > i; i++)
        {
            $('img#realm-' + result[i].id).attr("src", Plexis.template_url + '/images/realm-' + result[i].status + '-small.png');
            $('td#realm-' + result[i].id + '-uptime').html(result[i].uptime);
            $('td#realm-' + result[i].id + '-population').html(result[i].population);
        }
    }

});