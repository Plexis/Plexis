<div class="grid_12">
    <div class="block-border" id="tab-panel-1">
        <div class="block-header">
            <h1>Database Config & Operations</h1>
            <ul class="tabs">
                <li><a href="#tab-1">Configuration</a></li>
                <li><a href="#tab-2">Operations</a></li>
            </ul>
        </div>
        <div class="block-content tab-container">
            <!-- Hidden Message -->
            <div id="js_message" style="display: none;"></div>
            
            <form id="config-form" class="form" action="{SITE_URL}/ajax/database" method="post">
                <input type="hidden" name="action" value="save"/>
                <!-- Basic Settins -->
                <div id="tab-1" class="tab-content">
                    
                    <!-- Basic Information Field Set -->
                    <fieldset>
                        <legend>CMS Database</legend>
                        <div class="_100">
                            <p>
                                <label for="emu">Driver</label>
                                <select id="emu" name="cms_db_driver">
                                    <option value="mysql" <?php if('{config.DB.driver}' == 'mysql') echo "selected=\"selected\""; ?>>Mysql</option>
                                    <option value="postgre" <?php if('{config.DB.driver}' == 'postgre') echo "selected=\"selected\""; ?>>Postgre</option>
                                    <option value="sqlite" <?php if('{config.DB.driver}' == 'sqlite') echo "selected=\"selected\""; ?>>SQLite</option>
                                    <option value="oracle" <?php if('{config.DB.driver}' == 'oracle') echo "selected=\"selected\""; ?>>Oracle</option>
                                </select>
                            </p>
                            <p>
                                <label for="CMS Host">Hostname / IP</label>
                                <input id="CMS Host" name="cms_db_host" class="required" type="text" value="{config.DB.host}"/>
                            </p>
                            <p>
                                <label for="CMS Port">Port</label>
                                <input id="CMS Port" name="cms_db_port" class="required" type="text" value="{config.DB.port}"/>
                            </p>
                            <p>
                                <label for="CMS User">Username</label>
                                <input id="CMS User" name="cms_db_username" class="required" type="text" value="{config.DB.username}"/>
                            </p>
                            <p>
                                <label for="CMS Passowrd">Password</label>
                                <input id="CMS Password" name="cms_db_password" type="password" value="{config.DB.password}"/>
                            </p>
                            <p>
                                <label for="CMS Database">Database Name</label>
                                <input id="CMS Database" name="cms_db_database" class="required" type="database" value="{config.DB.database}"/>
                            </p>
                        </div>
                    </fieldset>
                    
                    <!-- Site Settings -->
                    <fieldset>
                        <legend>Realm Database</legend>
                        <div class="_100">
                            <p>
                                <label for="emu">Driver</label>
                                <select id="emu" name="cms_rdb_driver">
                                    <option value="mysql" <?php if('{config.RDB.driver}' == 'mysql') echo "selected=\"selected\""; ?>>Mysql</option>
                                    <option value="postgre" <?php if('{config.RDB.driver}' == 'postgre') echo "selected=\"selected\""; ?>>Postgre</option>
                                    <option value="sqlite" <?php if('{config.RDB.driver}' == 'sqlite') echo "selected=\"selected\""; ?>>SQLite</option>
                                    <option value="oracle" <?php if('{config.RDB.driver}' == 'oracle') echo "selected=\"selected\""; ?>>Oracle</option>
                                </select>
                            </p>
                            <p>
                                <label for="Realm Host">Hostname / IP</label>
                                <input id="Realm Host" name="cms_rdb_host" class="required" type="text" value="{config.RDB.host}"/>
                            </p>
                            <p>
                                <label for="Realm Port">Port</label>
                                <input id="Realm Port" name="cms_rdb_port" class="required" type="text" value="{config.RDB.port}"/>
                            </p>
                            <p>
                                <label for="Realm User">Username</label>
                                <input id="Realm User" name="cms_rdb_username" class="required" type="text" value="{config.RDB.username}"/>
                            </p>
                            <p>
                                <label for="Realm Passowrd">Password</label>
                                <input id="Realm Password" name="cms_rdb_password" type="password" value="{config.RDB.password}"/>
                            </p>
                            <p>
                                <label for="Realm Database">Database Name</label>
                                <input id="Realm Database" name="cms_rdb_database" class="required" type="database" value="{config.RDB.database}"/>
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