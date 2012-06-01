<div class="grid_12">

    <!-- Module Table -->
    <div class="block-border">
        <div class="block-header">
            <h1>Modules</h1><span></span>
        </div>
        <div class="block-content">
            <div id="js_message" style="display: none;"></div>
            <table id="module-table" class="table">
                <thead>
                    <tr>
                        <th>Module Name</th>
                        <th>Uri</th>
                        <th>Method(s)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Hidden Install form -->
<div id="install-modal">
    <div id="js_install_message"></div>
    <form id="install-form" class="form" action="{SITE_URL}/admin_ajax/modules" method="post">
        <input type="hidden" name="action" value="install">
        <div class="_100">
            <p>
                <div>
                    <label for="module">Module Name</label>
                    <input id="module" name="module" class="required" readonly="readonly" type="text" value="name" />
                </div>
            </p>
        
            <p>
                <div>
                    <label for="uri">Uri</label>
                    <input id="uri" name="uri" class="required" type="text" value="" />
                </div>
            </p>
            
            <p>
                <div>
                    <label for="function">function</label>
                    <input id="function" name="function" class="required" type="text" value="" />
                </div>
            </p>

            <div>
                <input id="submit" type="submit" class="button" style="width: 150px; text-align: center; margin: 10px; float: right;" value="Submit" />
            </div>
        </div>
    </form>
</div>