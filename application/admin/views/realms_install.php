<div class="grid_12">
    <div class="block-border">
        <div class="block-header">
            <h1>Installing realm "{realm.name}"</h1>
        </div>
        
        <form id="install-form" class="block-content form" action="{SITE_URL}/ajax/realms" method="post">
            <input type="hidden" name="action" value="install"/>
            <input type="hidden" name="id" value="{realm.id}"/>
            
            <!-- Hidden Message -->
            <div id="js_message" style="display: none;"></div>

            <!-- Basic Information Field Set -->
            <fieldset>
                <legend>Basic Settings</legend>
                <div class="_50">
                    <p>
                        <label for="name">Realm Name</label>
                        <input id="name" name="name" class="required" type="text" readonly="readonly" value="{realm.name}"/>
                    </p>
                    <p>
                        <label for="address">Realm IP Address</label>
                        <input id="address" name="address" class="required" type="text" value="{realm.address}"/>
                    </p>
                    <p>
                        <label for="port">Realm Port #</label>
                        <input id="port" name="port" class="required" type="text" value="{realm.port}"/>
                    </p>
                    <p>
                        <label for="type">Realm Type</label>
                        <select id="type" name="type">
                            <option value="PvP">PvP</option>
                            <option value="Normal">Normal</option>
                            <option value="RP">Role Playing</option>
                            <option value="RPPvP">Role Playing PvP</option>
                            <option value="FFA_PvP">Free For All</option>
                        </select>
                    </p>
                    <p>
                        <label for="type">WoWLib Driver</label>
                        <select id="driver" name="driver" title="This is the WoWLib file that best suites your current realm version. Please visit the forums if you are still unclear here.">
                            <?php foreach($drivers as $driver): ?>
                                <option value="<?php echo $driver; ?>"><?php echo $driver; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                </div>
            </fieldset>
            
            <!-- Character Database Settings -->
            <fieldset>
                <legend>Character Database Settings</legend>
                <div class="_50">
                    <p>
                        <label for="c_driver">Driver</label>
                        <select id="c_driver" name="c_driver">
                            <option value="mysql">Mysql</option>
                            <option value="postgre">Postgre</option>
                        </select>
                    </p>
                    <p>
                        <label for="c_address">Host / IP Address</label>
                        <input id="c_address" name="c_address" class="required" type="text" value="{realm.address}"/>
                    </p>
                    <p>
                        <label for="c_port">Port</label>
                        <input id="c_port" name="c_port" class="required" type="text" value="3306"/>
                    </p>
                    <p>
                        <label for="c_username">Username</label>
                        <input id="c_username" name="c_username" class="required" type="text" value=""/>
                    </p>
                    <p>
                        <label for="c_password">Password</label>
                        <input id="c_password" name="c_password" class="required" type="password" value=""/>
                    </p>
                    <p>
                        <label for="c_database">Database Name</label>
                        <input id="c_database" name="c_database" class="required" type="text" value="characters"/>
                    </p>
                </div>
            </fieldset>
            
            <!-- World Database Settings -->
            <fieldset>
                <legend>World Database Settings</legend>
                <div class="_50">
                    <p>
                        <label for="w_driver">Driver</label>
                        <select id="w_driver" name="w_driver">
                            <option value="mysql">Mysql</option>
                            <option value="postgre">Postgre</option>
                        </select>
                    </p>
                    <p>
                        <label for="w_address">Host / IP Address</label>
                        <input id="w_address" name="w_address" class="required" type="text" value="{realm.address}"/>
                    </p>
                    <p>
                        <label for="w_port">Port</label>
                        <input id="w_port" name="w_port" class="required" type="text" value="3306"/>
                    </p>
                    <p>
                        <label for="w_username">Username</label>
                        <input id="w_username" name="w_username" class="required" type="text" value=""/>
                    </p>
                    <p>
                        <label for="w_password">Password</label>
                        <input id="w_password" name="w_password" class="required" type="password" value=""/>
                    </p>
                    <p>
                        <label for="w_database">Database Name</label>
                        <input id="w_database" name="w_database" class="required" type="text" value="world"/>
                    </p>
                </div>
            </fieldset>
            
            <!-- RA Settings -->
            <fieldset>
                <legend>Remote Access</legend>
                <div class="_50">
                    <p>
                        <label for="ra_type">Remote Access Type</label>
                        <select id="ra_type" name="ra_type" title="Warning: If you are using and older version of Trinity, SOAP probably will not work!">
                            <option value="TELNET">Telnet</option>
                            <option value="SOAP">SOAP</option>
                        </select>
                    </p>
                    <p>
                        <label for="ra_port">Port</label>
                        <input id="ra_port" name="ra_port" type="text" value="3443"/>
                    </p>
                    <p>
                        <label for="ra_username">Username</label>
                        <input id="ra_username" name="ra_username" type="text" title="This needs to be a level 3+ admin account on your server"/>
                    </p>
                    <p>
                        <label for="ra_password">Password</label>
                        <input id="ra_password" name="ra_password" type="password" value=""/>
                    </p>
                    <p>
                        <label for="ra_urn">Urn / Uri</label>
                        <input id="ra_urn" name="ra_urn" type="text" value="" title="Soap Only. Custom server urn/uri. Leave blank if you are unsure."/>
                    </p>
                </div>
            </fieldset>

            <!-- Submit Buttons -->
            <div class="block-actions">
                <ul class="actions-left">
                    <li><a class="button red" href="{SITE_URL}/admin/realms">Cancel</a></li>
                </ul>
                <ul class="actions-right">
                    <li><input type="submit" class="button" value="Install"></li>
                </ul>
            </div>
        </form>
    </div>
</div>