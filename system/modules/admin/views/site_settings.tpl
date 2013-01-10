<div class="grid_12">
    <div class="block-border" id="tab-panel-1">
        <div class="block-header">
            <h1>Site Settings</h1>
            <ul class="tabs">
                <li><a href="#tab-1">Basic</a></li>
                <li><a href="#tab-2">Advanced</a></li>
            </ul>
        </div>
        
        <div class="block-content tab-container">
            <!-- Hidden Message -->
            <div id="js_message" style="display: none;"></div>
            
            <form id="config-form" class="form" action="{SITE_URL}/admin_ajax/settings" method="post">
                <input type="hidden" name="action" value="save"/>
                
                
                <!-- Basic Settins [ TAB 1 ] -->
                <div id="tab-1" class="tab-content">
                    <!-- Basic Information Field Set -->
                    <fieldset>
                        <legend>Meta Settings</legend>
                        <div class="_100">
                            <p>
                                <label for="Site Title">Site Title</label>
                                <input id="Site Title" name="cfg__site_title" class="required" type="text" value="{config.site_title}"/>
                            </p>
                            <p>
                                <label for="Site Desc">Site Description</label>
                                <input id="Site Desc" name="cfg__meta_description" type="text" value="{config.meta_description}"/>
                            </p>
                            <p>
                                <label for="Site Key">Site Keywords</label>
                                <input id="Site Key" name="cfg__meta_keywords" type="text" value="{config.meta_keywords}"/>
                            </p>
                        </div>
                    </fieldset>
                    
                    <!-- Site Settings -->
                    <fieldset>
                        <legend>Site Settings</legend>
                        <div class="_100">
                            <p>
                                <label for="Site Email">Site Owner Email</label>
                                <input id="Site Email" name="cfg__site_email" class="required" type="text" value="{config.site_email}"/>
                            </p>
                            <p>
                                <label for="Site SEmail">Site Support Email</label>
                                <input id="Site SEmail" name="cfg__site_support_email" class="required" type="text" value="{config.site_support_email}"/>
                            </p>
                            <p>
                                <label for="theme">Default Template</label>
                                <select id="theme" name="cfg__default_template">
                                    {options.templates}
                                        {value}
                                    {/options.templates}
                                </select>
                            </p>
                            <p>
                                <label for="Language">Default Language</label>
                                <select id="Language" name="cfg__default_language">
                                    {options.languages}
                                        {value}
                                    {/options.languages}
                                </select>
                            </p>
                        </div>
                    </fieldset>
                    
                    <!-- WoW Settings -->
                    <fieldset>
                        <legend>WoW Settings</legend>
                        <div class="_50">
                            <p>
                                <label for="Site Email">Logon Server (Set Realmlist)</label>
                                <input id="Site Email" name="cfg__logon_server" class="required" type="text" value="{config.logon_server}"/>
                            </p>
                            <p>
                                <label for="emu">Emulator</label>
                                <select id="emu" name="cfg__emulator">
                                    {options.emulators}
                                        {value}
                                    {/options.emulators}
                                </select>
                            </p>
                            <p>
                                <label for="Default Realm">Default Realm</label>
                                <select id="Default Realm" name="cfg__default_realm_id">
                                    {options.realms}
                                        {value}
                                    {/options.realms}
                                </select>
                            </p>
                        </div>
                    </fieldset>
                </div>
                
                <!-- Advanced Settins [ TAB 2 ] -->
                <div id="tab-2" class="tab-content">
                    <!-- Account Settings -->
                    <fieldset>
                        <legend>Account Settings</legend>
                        <div class="_50">
                            <p>
                                <label for="Pass_recovery">Send Email On Password Change</label>
                                <select id="Pass_recovery" name="cfg__send_email_pass_change">
                                    <option value="1" <?php if('{config.send_email_pass_change}' == 1) echo "selected=\"selected\""; ?>>Enabled</option>
                                    <option value="0" <?php if('{config.send_email_pass_change}' == 0) echo "selected=\"selected\""; ?>>Disabled</option>
                                </select>
                            </p>
                        </div>
                    </fieldset>
                </div>
                
                <div class="block-actions">
                    <ul class="actions-left">
                        <li><a class="button red" href="{SITE_URL}/admin/settings">Undo Changes</a></li>
                    </ul>
                    <ul class="actions-right">
                        <li><input type="submit" class="button" value="Apply Changes"></li>
                    </ul>
                </div>
            </form>
        </div>
    </div>
</div>