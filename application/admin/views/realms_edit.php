<div class="grid_12">
    <div class="block-border">
        <div class="block-header">
            <h1>Updating realm "{realm.name}"</h1>
        </div>
        
        <form id="edit-form" class="block-content form" action="{SITE_URL}/ajax/realms" method="post">
            <input type="hidden" name="action" value="edit"/>
            <input type="hidden" name="name" value="{realm.name}"/>
            <input type="hidden" name="id" value="{realm.id}"/>
            
            <!-- Hidden Message -->
            <div id="js_message" style="display: none;"></div>

            <!-- Basic Information Field Set -->
            <fieldset>
                <legend>Basic Settings</legend>
                <div class="_50">
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
                            <option value="PvP" <?php if('{realm.type}' == 'PvP') echo "selected=\"selected\""; ?>>PvP</option>
                            <option value="Normal" <?php if('{realm.type}' == 'Normal') echo "selected=\"selected\""; ?>>Normal</option>
                            <option value="RP" <?php if('{realm.type}' == 'RP') echo "selected=\"selected\""; ?>>Role Playing</option>
                            <option value="RPPvP" <?php if('{realm.type}' == 'RPPvP') echo "selected=\"selected\""; ?>>Role Playing PvP</option>
                            <option value="FFA_PvP" <?php if('{realm.type}' == 'FFA_PvP') echo "selected=\"selected\""; ?>>Free For All</option>
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
                        <input id="c_address" name="c_address" class="required" type="text" value="{realm.cdb.host}"/>
                    </p>
                    <p>
                        <label for="c_port">Port</label>
                        <input id="c_port" name="c_port" class="required" type="text" value="{realm.cdb.port}"/>
                    </p>
                    <p>
                        <label for="c_username">Username</label>
                        <input id="c_username" name="c_username" class="required" type="text" value="{realm.cdb.username}"/>
                    </p>
                    <p>
                        <label for="c_password">Password</label>
                        <input id="c_password" name="c_password" class="required" type="password" value="{realm.cdb.password}"/>
                    </p>
                    <p>
                        <label for="c_database">Database Name</label>
                        <input id="c_database" name="c_database" class="required" type="text" value="{realm.cdb.database}"/>
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
                        <input id="w_address" name="w_address" class="required" type="text" value="{realm.wdb.host}"/>
                    </p>
                    <p>
                        <label for="w_port">Port</label>
                        <input id="w_port" name="w_port" class="required" type="text" value="{realm.wdb.port}"/>
                    </p>
                    <p>
                        <label for="w_username">Username</label>
                        <input id="w_username" name="w_username" class="required" type="text" value="{realm.wdb.username}"/>
                    </p>
                    <p>
                        <label for="w_password">Password</label>
                        <input id="w_password" name="w_password" class="required" type="password" value="{realm.wdb.password}"/>
                    </p>
                    <p>
                        <label for="w_database">Database Name</label>
                        <input id="w_database" name="w_database" class="required" type="text" value="{realm.wdb.database}"/>
                    </p>
                </div>
            </fieldset>
            
            <!-- RA Settings -->
            <fieldset>
                <legend>Remote Access</legend>
                <div class="_50">
                    <p>
                        <label for="ra_type">Remote Access Type</label>
                        <select id="ra_type" name="ra_type" title="Warning: If you are using Trinity, SOAP probably will not work!">
                            <option value="TELNET" <?php if('{realm.ra.type}' == 'TELNET') echo "selected=selected"; ?>>Telnet</option>
                            <option value="SOAP" <?php if('{realm.ra.type}' == 'SOAP') echo "selected=selected"; ?>>SOAP</option>
                        </select>
                    </p>
                    <p>
                        <label for="ra_port">Port</label>
                        <input id="ra_port" name="ra_port" type="text" value="{realm.ra.port}"/>
                    </p>
                    <p>
                        <label for="ra_username">Username</label>
                        <input id="ra_username" name="ra_username" type="text" value="{realm.ra.username}" title="This needs to be a level 3+ admin account on your server"/>
                    </p>
                    <p>
                        <label for="ra_password">Password</label>
                        <input id="ra_password" name="ra_password" type="password" value="{realm.ra.password}"/>
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
                    <li><input type="submit" class="button" value="Update"></li>
                </ul>
            </div>
        </form>
    </div>
</div>
<script type='text/javascript'>
    $().ready(function() {
        // Tipsy mouseovers
        $('select[name=driver]').tipsy({trigger: 'hover', gravity: 's', delayIn: 500, delayOut: 500});
        $('select[name=ra_type]').tipsy({trigger: 'hover', gravity: 's', delayIn: 500, delayOut: 500});
        $('input[name=ra_username]').tipsy({trigger: 'hover', gravity: 's', delayIn: 500, delayOut: 500});
        $('input[name=ra_urn]').tipsy({trigger: 'hover', gravity: 's', delayIn: 500, delayOut: 500});
        
        // Form validation
        $("#edit-form").validate();
        
        // ===============================================
        // bind the Update form using 'ajaxForm' 
        $('#edit-form').ajaxForm({
            beforeSubmit: function (arr, data, options){
                $('#js_message').attr('class', 'alert loading').html('Submitting Form...').slideDown(300);
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return true;
            },
            success: result,
            timeout: 5000 
        });

        // Callback function for the Install ajaxForm 
        function result(response, statusText, xhr, $form)  { 
            // Parse the JSON response
            var result = jQuery.parseJSON(response);
            if (result.success == true){
                // Display our Success message, and ReDraw the table so we imediatly see our action
                $('#edit-form').html('<div class="alert ' + result.type +'">' + result.message + '. <a href="{SITE_URL}/admin/realms">Click here to return.</a></div>');
            }else{
                $('#js_message').attr('class', 'alert ' + result.type).html(result.message);
            }
            $('#js_message').delay(5000).slideUp(300);
        }
    });
</script>