<script type="text/javascript">
    $().ready(function() {
        /*
         * DataTables
         */
        $('#data-table').dataTable({
            "bServerSide": true,
            "bSortClasses": false,
            "sAjaxSource": "{SITE_URL}/ajax/users",
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
    })
</script>

<div class="grid_12">
    <div class="block-border">
        <div class="block-header">
            <h1>User Accounts</h1><span></span>
        </div>
        <div class="block-content">
            <table id="data-table" class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Account Level</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>