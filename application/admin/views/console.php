<div class="grid_8">
    <div class="block-border">
        <div class="block-header">
            <h1>Remote Access</h1><span></span>
        </div>
        <div class="block-content">
            <div id="js_message" style="display: none;"></div>
            <div class="c_selector">{realm_selector}</div>
            <br /><br /><br />
            <div id="console"><span class="c_prefix">$</span> Loading...</div>
            <input id="command" disabled="disabled" onkeyup="execute(this,event);" />
        </div>
    </div>
</div>

<div class="grid_4">
    <div class="block-border">
        <div class="block-header">
            <h1>Console Commands</h1><span></span>
        </div>
        <div class="block-content">
            <table id="data-table" class="table">
                <thead>
                    <th width="30%">Action</th>
                    <th>Syntax</th>
                </thead>
                <tbody>
                    <tr>
                        <td>Logging in:</td>
                        <td>login #username #password</td>
                    </tr>
                    <tr>
                        <td>Logging out:</td>
                        <td>logout</td>
                    </tr>
                    <tr>
                        <td>Clear Console:</td>
                        <td>clear</td>
                    </tr>
                    <tr>
                        <td>Custom Connection:</td>
                        <td>connect #host #port #ra_type(soap or telnet) #urn (Soap only and Optional)</td>
                    </tr>
                    <tr>
                        <td>Disconnect (custom):</td>
                        <td>disconnect</td>
                    </tr>
                    <tr>
                        <td>Scroll commands:</td>
                        <td>Up and Down arrow keys</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add the console css to the head tag -->
<pcms::eval>
    <?php
        $this->append_metadata('');
        $this->append_metadata('<!-- Include console css file -->');
        $this->append_metadata('<link rel="stylesheet" href="'. SITE_URL .'/application/admin/css/console.css"/>'); 
    ?>
</pcms::eval>