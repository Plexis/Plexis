<!-- Button -->
<div>
    <a id="create" href="javascript:void(0);" class="button">Add New Vote Site</a>
</div>

<!-- News List Table -->
<div class="block-border">
    <div class="block-header">
        <h1>Vote Sites</h1><span></span>
    </div>
    <div class="block-content">
        <div id="js_message" style="display: none;"></div>
        <table id="data-table" class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hostname</th>
                    <th>Vote Link</th>
                    <th>Points</th>
                    <th>Reset Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Hidden Create / Edit form -->
<div id="vote-form" title="Create Vote Site" style="display: none;">
    <div id="js_news_message" style="display: none;"></div>
    <form id="vote" class="form" action="{SITE_URL}/admin_ajax/vote" method="post">
        <input id="formtype" type="hidden" name="action" value="create">
        <input id="vote_id" type="hidden" name="id" value="0">
        <div class="_100">
            <p>
                <label for="hostname">Hostname</label>
                <input id="hostname" name="hostname" class="required" type="text" value="" />
            </p>
        
            <p>
                <label for="votelink">Vote Link</label>
                <input id="votelink" name="votelink" class="required" type="text" value="" />
            </p>
            
            <p>
                <label for="image_url">Image Url</label>
                <input id="image_url" name="image_url" type="text" value="" />
            </p>
        </div>
        
        <div class="_25">
            <p>
                <label for="points">Reward Points</label>
                <input id="points" name="points" type="text" value="0" />
            </p>
            
            <p>
                <label for="reset_time">Reset Time</label>
                <select id="reset_time" name="reset_time">
                    <option value="43200">12 Hours</option>
                    <option value="86400">24 Hours</option>
                </select>
            </p>
        </div>

        <div class="_100">
            <center><input id="form-submit" type="submit" class="button" style="width: 150px; text-align: center;" value="Submit"></center>
        </div>

    </form>
</div>