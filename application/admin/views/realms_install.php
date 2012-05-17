<div class="grid_12">
    <div class="block-border" id="tab-panel-1">
        <div class="block-header">
            <h1>Installing realm "{realm.name}"</h1>
            <ul class="tabs">
                <li><a href="#tab-1">Basic Info</a></li>
                <li><a href="#tab-2">Database / RA Settings</a></li>
            </ul>
        </div>
        
        <div class="block-content tab-container">
            <form id="install-form" class="form" action="{SITE_URL}/admin_ajax/realms" method="post">
                <input type="hidden" name="action" value="install"/>
                <input type="hidden" name="id" value="{realm.id}"/>
                
                <!-- TAB 1 -->
                <div id="tab-1" class="tab-content">
                    <!-- Hidden Message -->
                    <div id="js_message" style="display: none;"></div>

                    <!-- Basic Information Field Set -->
                    <fieldset>
                        <legend>Basic Information</legend>
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
                                <label for="max_players">Max Players</label>
                                <input id="max_players" name="max_players" class="required" type="text" value="500"/>
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
                    
                    <fieldset>
                       <legend>Server Rates</legend>
                        <div class="_100">
                            <p>
                                <label for="rates_desc">Realm Overall Desc</label>
                                <select id="rates_desc" name="rates_desc">
                                    <option value="blizzlike">Blizzlike</option>
                                    <option value="instant_80">Instant 80</option>
                                    <option value="instant_85">Instant 85</option>
                                    <option value="fun_server">Fun Server</option>
                                    <option value="gm_realm">GM Realm</option>
                                </select>
                            </p>
                        </div>
                        
                        <div class="_50">
                            <p>
                                <label for="rates_xp">Xp Rates</label>
                                <select id="rates_xp" name="rates_xp">
                                    <option value="1">Blizzlike</option>
                                    <option value="2">2x Blizzlike</option>
                                    <option value="3">3x Blizzlike</option>
                                    <option value="4">4x Blizzlike</option>
                                    <option value="5">5x Blizzlike</option>
                                    <option value="6">6x Blizzlike</option>
                                    <option value="7">7x Blizzlike</option>
                                    <option value="8">8x Blizzlike</option>
                                    <option value="9">9x Blizzlike</option>
                                    <option value="10">10x Blizzlike</option>
                                    <option value="12">12x Blizzlike</option>
                                    <option value="15">15x Blizzlike</option>
                                    <option value="20">20x Blizzlike</option>
                                    <option value="25">25+ xBlizzlike</option>
                                </select>
                            </p>
                            <p>
                                <label for="rates_drop">Drop Rates</label>
                                <select id="rates_drop" name="rates_drop">
                                    <option value="1">Blizzlike</option>
                                    <option value="2">2x Blizzlike</option>
                                    <option value="3">3x Blizzlike</option>
                                    <option value="4">4x Blizzlike</option>
                                    <option value="5">5x Blizzlike</option>
                                    <option value="6">6x Blizzlike</option>
                                    <option value="7">7x Blizzlike</option>
                                    <option value="8">8x Blizzlike</option>
                                    <option value="9">9x Blizzlike</option>
                                    <option value="10">10x Blizzlike</option>
                                    <option value="12">12x Blizzlike</option>
                                    <option value="15">15x Blizzlike</option>
                                    <option value="20">20x Blizzlike</option>
                                    <option value="25">25+ xBlizzlike</option>
                                </select>
                            </p>
                            <p>
                                <label for="rates_gold">Money Rates</label>
                                <select id="rates_gold" name="rates_gold">
                                    <option value="1">Blizzlike</option>
                                    <option value="2">2x Blizzlike</option>
                                    <option value="3">3x Blizzlike</option>
                                    <option value="4">4x Blizzlike</option>
                                    <option value="5">5x Blizzlike</option>
                                    <option value="6">6x Blizzlike</option>
                                    <option value="7">7x Blizzlike</option>
                                    <option value="8">8x Blizzlike</option>
                                    <option value="9">9x Blizzlike</option>
                                    <option value="10">10x Blizzlike</option>
                                    <option value="12">12x Blizzlike</option>
                                    <option value="15">15x Blizzlike</option>
                                    <option value="20">20x Blizzlike</option>
                                    <option value="25">25+ xBlizzlike</option>
                                </select>
                            </p>
                        </div>
                        
                        <div class="_50">
                            <p>
                                <label for="rates_professions">Profession Rates</label>
                                <select id="rates_professions" name="rates_professions">
                                    <option value="1">Blizzlike</option>
                                    <option value="2">2x Blizzlike</option>
                                    <option value="3">3x Blizzlike</option>
                                    <option value="4">4x Blizzlike</option>
                                    <option value="5">5x Blizzlike</option>
                                    <option value="6">6x Blizzlike</option>
                                    <option value="7">7x Blizzlike</option>
                                    <option value="8">8x Blizzlike</option>
                                    <option value="9">9x Blizzlike</option>
                                    <option value="10">10x Blizzlike</option>
                                    <option value="12">12x Blizzlike</option>
                                    <option value="15">15x Blizzlike</option>
                                    <option value="20">20x Blizzlike</option>
                                    <option value="25">25+ xBlizzlike</option>
                                </select>
                            </p>
                            <p>
                                <label for="rates_reputation">Reputation Rates</label>
                                <select id="rates_reputation" name="rates_reputation">
                                    <option value="1">Blizzlike</option>
                                    <option value="2">2x Blizzlike</option>
                                    <option value="3">3x Blizzlike</option>
                                    <option value="4">4x Blizzlike</option>
                                    <option value="5">5x Blizzlike</option>
                                    <option value="6">6x Blizzlike</option>
                                    <option value="7">7x Blizzlike</option>
                                    <option value="8">8x Blizzlike</option>
                                    <option value="9">9x Blizzlike</option>
                                    <option value="10">10x Blizzlike</option>
                                    <option value="12">12x Blizzlike</option>
                                    <option value="15">15x Blizzlike</option>
                                    <option value="20">20x Blizzlike</option>
                                    <option value="25">25+ xBlizzlike</option>
                                </select>
                            </p>
                            <p>
                                <label for="rates_honor">Honor Rates</label>
                                <select id="rates_honor" name="rates_honor">
                                    <option value="1">Blizzlike</option>
                                    <option value="2">2x Blizzlike</option>
                                    <option value="3">3x Blizzlike</option>
                                    <option value="4">4x Blizzlike</option>
                                    <option value="5">5x Blizzlike</option>
                                    <option value="6">6x Blizzlike</option>
                                    <option value="7">7x Blizzlike</option>
                                    <option value="8">8x Blizzlike</option>
                                    <option value="9">9x Blizzlike</option>
                                    <option value="10">10x Blizzlike</option>
                                    <option value="12">12x Blizzlike</option>
                                    <option value="15">15x Blizzlike</option>
                                    <option value="20">20x Blizzlike</option>
                                    <option value="25">25+ xBlizzlike</option>
                                </select>
                            </p>
                        </div>
                    </fieldset>  
                    <div class="alert info">Please see second tab</div>
                </div>
                
                <!-- TAB 2 -->
                <div id="tab-2" class="tab-content">
                    <!-- Character Database Settings -->
                    <fieldset>
                        <legend>Character Database Settings</legend>
                        <div class="_50">
                            <p>
                                <label for="c_driver">Driver</label>
                                <select id="c_driver" name="c_driver">
                                    <option value="mysql">Mysql</option>
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
                                <select id="ra_type" name="ra_type">
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
                </div>
            </form>
        </div>
    </div>
</div>