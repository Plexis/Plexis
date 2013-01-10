<div class="grid_12">
    <div class="block-border">
        <div class="block-header">
            <h1>Characters</h1><span></span>
        </div>
        <div class="block-content">
            <p style="text-align: center;">
                <label for="realm-changer"><b>Realm:</b></label>
                <select id="realm-changer" name="realm">
                    {realms}
                        {value}
                    {/realms}
                </select>
            </p>
            <table id="character-table" class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Level</th>
                        <th>Race</th>
                        <th>Class</th>
                        <th>Zone</th>
                        <th>Owner</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>