<div class="left-box">
    <h2>Online List</h2>
    <div class="left-box-content">
        <div id="realm-name" style="text-align: center;"><h3>{realm_name}</h3></div>
        <br />
        <center>
            Change Realm: <br />
            <select id="realm-changer">
                {realm_options}
                    {value}
                {/realm_options}
            </select>
        </center>
        <br /><br />
        <table id="online-table" style="clear: both; text-align: center;">
            <thead>
                <tr>
                    <th scope="col" width="25px;">Id</th>
                    <th scope="col">Name</th>
                    <th scope="col" width="30px;">Level</th>
                    <th scope="col" width="30px;">Race</th>
                    <th scope="col" width="30px;">Class</th>
                    <th scope="col">Zone</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>