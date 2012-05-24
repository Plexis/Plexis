<div class="left-box">
    <h2>Vote Menu</h2>
    <div class="left-box-content">
        <table style="clear: both; text-align: center;">
            <thead>
                <tr>
                    <th scope="col">Site</th>
                    <?php if(config('web_points_enabled')): ?>
                        <th scope="col">Points</th>
                    <?php endif; ?>
                    <th scope="col">Time Left</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                {sites}
                    <tr>
                        <td><img src="{image_url}" alt="{hostname}" /></td>
                        <?php if(config('web_points_enabled')): ?>
                            <td>{points}</td>
                        <?php endif; ?>
                        <td>{time_left}</td>
                        <td><button id="vote" name="{id}" class="button" {disabled}>Vote</button></td>
                    </tr>
                {/sites}
            </tbody>
        </table>
        
        <!-- Hidden Vote Window Lock Div -->
        <div id="vote-window">Locked until voting is complete, Please do not Close or Refresh this window.</div>
    </div>
</div>