<div class="grid_12">
    <!-- Button -->
    <div><a id="regkey" href="javascript:void(0);" class="button">Registration Keys</a></div>

    <div class="block-border" id="tab-panel-1">
        <div class="block-header">
            <h1>Registration Settings</h1>
        </div>
        <div class="block-content tab-container">
            <!-- Hidden Message -->
            <div id="js_message" style="display: none;"></div>
            
            <!-- Basic Settins -->
            <form id="config-form" class="form" action="{SITE_URL}/admin_ajax/settings" method="post">
                <input type="hidden" name="action" value="save"/>
            
                <fieldset>
                    <legend>Basic Settings</legend>
                    <div class="_50">
                        <p>
                            <label for="Registration" title="Disabling guest registration will prevent anyone from creating new accounts on your server">
                                Guest Registration
                            </label>
                            <select id="Registration" name="cfg__allow_registration">
                                <option value="1" <?php if($config['allow_registration'] === 1) echo "selected=\"selected\""; ?>>Enabled</option>
                                <option value="0" <?php if($config['allow_registration'] === 0) echo "selected=\"selected\""; ?>>Disabled</option>
                            </select>
                        </p>
                        <p>
                            <label for="Req Key" title="If enabled, Guests will need to enter a cpatcha.">
                                Enable Captcha
                            </label>
                            <select id="enable_captcha" name="cfg__enable_captcha">
                                <option value="1" <?php if($config['enable_captcha'] === 1) echo "selected=\"selected\""; ?>>Enabled</option>
                                <option value="0" <?php if($config['enable_captcha'] === 0) echo "selected=\"selected\""; ?>>Disabled</option>
                            </select>
                        </p>
                    </div>
                    <div class="_50">
                        <p>
                            <label for="Req Email" title="If Enabled, Requires users to activate thier account via Email before being able to login and play">
                                Require Email Activation
                            </label>
                            <select id="Req Email" name="cfg__reg_email_verification">
                                <option value="1" <?php if($config['reg_email_verification'] === 1) echo "selected=\"selected\""; ?>>Enabled</option>
                                <option value="0" <?php if($config['reg_email_verification'] === 0) echo "selected=\"selected\""; ?>>Disabled</option>
                            </select>
                        </p>
                        <p>
                            <label for="Unique" title="If enabled, Users cannot create an account using an already used email address.">
                                One Account per Email
                            </label>
                            <select id="Unique" name="cfg__reg_unique_email">
                                <option value="1" <?php if($config['reg_unique_email'] === 1) echo "selected=\"selected\""; ?>>Enabled</option>
                                <option value="0" <?php if($config['reg_unique_email'] === 0) echo "selected=\"selected\""; ?>>Disabled</option>
                            </select>
                        </p>
                    </div>
                </fieldset>
                
                <fieldset>
                    <legend>Registration Key Settings</legend>
                    
                    <div class="_50">
                        <p>
                            <label for="Req Key" title="If enabled, Guests will need a registration key before being able to create an account">
                                Require Registration Key
                            </label>
                            <select id="Req Key" name="cfg__reg_registration_key">
                                <option value="1" <?php if($config['reg_registration_key'] === 1) echo "selected=\"selected\""; ?>>Enabled</option>
                                <option value="0" <?php if($config['reg_registration_key'] === 0) echo "selected=\"selected\""; ?>>Disabled</option>
                            </select>
                        </p>
                    </div>
                </fieldset>
                <!-- Form Buttons -->
                <div class="block-actions">
                    <ul class="actions-left">
                        <li><a class="button red" href="{SITE_URL}/admin/registration">Undo Changes</a></li>
                    </ul>
                    <ul class="actions-right">
                        <li><input type="submit" class="button" value="Apply Changes"></li>
                    </ul>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Key form -->
<div id="key-modal">
    <div id="js_key_message"></div>
    <form id="key-form" class="form" action="" method="">
        <div class="_100">
            <p>
                <div>
                    <label for="genkey">Key</label>
                    <input id="genkey" readonly="readonly" type="text"/>
                </div>
            </p>

            <div>
                <a id="delete" href="javascript:void(0);" class="button red" style="width: 150px; text-align: center; margin: 10px; float: left;">Delete Unassinged Keys</a>
                <a id="generate" href="javascript:void(0);" class="button" style="width: 150px; text-align: center; margin: 10px; float: right;">Generate Key</a>
            </div>
        </div>
    </form>
</div>