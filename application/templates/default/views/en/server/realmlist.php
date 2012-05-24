<div class="left-box">
    <h2>Realm List</h2>
    <div class="left-box-content">
        <table id="data-table" cellpadding="0" cellspacing="0" border="0" class="datatable" style="clear: both; text-align: center;">
            <thead>
                <tr>
                    <th scope="col">Status</th>
                    <th scope="col">Name</th>
                    <th scope="col">Uptime</th>
                    <th scope="col">Type</th>
                    <th scope="col">Population</th>
                </tr>
            </thead>
            <tbody>
                {realms}
                    <tr>
                        <td><center><img id="realm-{id}" src='{BASE_URL}/application/static/images/icons/loading.gif'></center></td>
                        <td><a href="{SITE_URL}/server/viewrealm/{id}">{name}</a></td>
                        <td id="realm-{id}-uptime"></td>
                        <td>{type}</td>
                        <td id="realm-{id}-population"></td>
                    </tr>
                {/realms}
            </tbody>
        </table>
    </div>
</div>