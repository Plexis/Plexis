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
            
            <form id="config-form" class="form" action="{SITE_URL}/ajax/settings" method="post">
                <input type="hidden" name="action" value="save"/>
                <!-- Basic Settins -->
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
                        </div>
                    </fieldset>
                    
                    <!-- Site Settings -->
                    <fieldset>
                        <legend>Site Settings</legend>
                        <div class="_100">
                            <p>
                                <label for="Site Email">Site Email</label>
                                <input id="Site Email" name="cfg__site_email" class="required" type="text" value="{config.site_email}"/>
                            </p>
                            <p>
                                <label for="theme">Default Template</label>
                                <select id="theme" name="cfg__default_template">
                                    <option>{config.default_template}</option>
                                </select>
                            </p>
                            <p>
                                <label for="Language">Default Language</label>
                                <select id="Language" name="cfg__default_language">
                                    <option>{config.default_language}</option>
                                </select>
                            </p>
                        </div>
                    </fieldset>
                    
                    <!-- WoW Settings -->
                    <fieldset>
                        <legend>WoW Settings</legend>
                        <div class="_50">
                            <p>
                                <label for="emu">Emulator</label>
                                <select id="emu" name="cfg__emulator">
                                    <option value="mangos" <?php if('{config.emulator}' == 'mangos') echo "selected=\"selected\""; ?>>Mangos</option>
                                    <option value="mangos" <?php if('{config.emulator}' == 'trinity') echo "selected=\"selected\""; ?>>Trinity</option>
                                    <option value="mangos" <?php if('{config.emulator}' == 'arcemu') echo "selected=\"selected\""; ?>>ArcEmu</option>
                                    <option value="mangos" <?php if('{config.emulator}' == 'skyfire') echo "selected=\"selected\""; ?>>Skyfire</option>
                                </select>
                            </p>
                            <p>
                                <label for="Default Realm">Default Realm</label>
                                <select id="Default Realm" name="cfg__default_realm_id">
                                    <option value="1">No Realms Installed!</option>
                                </select>
                            </p>
                        </div>
                    </fieldset>
                </div>
                
                <!-- More Tab -->
                <div id="tab-2" class="tab-content">
                    <p><div class="alert info">More to come!</div></p>
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