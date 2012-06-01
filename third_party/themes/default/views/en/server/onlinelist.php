<div class="left-box">
    <h2>Online List</h2>
    <div class="left-box-content">
        <div id="realm-name" style="text-align: center;"><h3>{realm_name}</h3></div>
        <br />
        <center>
            Change Realm: <br />
            <select id="realm-changer" style="min-width:150px; text-align:center;">
                {realm_options}
                    {value}
                {/realm_options}
            </select>
        </center>
        <br /><br />
        <table id="online-table" cellpadding="0" cellspacing="0" border="0" class="datatable" style="clear: both; text-align: center;">
            <thead>
                <tr>
                    <th scope="col">Id</th>
                    <th scope="col">Name</th>
                    <th scope="col">Level</th>
                    <th scope="col">Race</th>
                    <th scope="col">Class</th>
                    <th scope="col">Zone</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>