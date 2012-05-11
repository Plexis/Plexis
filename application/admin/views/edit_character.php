<div class="grid_8">
    <div class="block-border">
        <div class="block-header">
            <h1>Editing Character</h1>
        </div>
        
        <!-- Profile Form -->
        <form id="profile" class="block-content form" action="{SITE_URL}/admin_ajax/characters" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="realm" value="{realm}">
            <input type="hidden" name="id" value="{character.id}">
            
            <!-- JS Ajax message for profile updates -->
            <div id="js_message" style="display: none;"></div>
            
            <!-- Basic Information Field Set -->
            <fieldset>
                <legend>Basic Information</legend>
                <div class="_50">
                    <p>
                        <label for="name">Name</label>
                        <input id="name" name="name" type="text" value="{character.name}"/>
                    </p>
                </div>
                
                <div class="_50">
                    <p>
                        <label for="level">Level</label>
                        <input id="level" name="level" type="text" value="{character.level}"/>
                    </p>
                </div>
                
                <div class="_50">
                    <p>
                        <label for="money">Money</label>
                        <input id="money" name="money" type="text" value="{character.money}"/>
                    </p>
                </div>
                
                <div class="_50">
                    <p>
                        <label for="xp">Experience</label>
                        <input id="xp" name="xp" type="text" value="{character.xp}"/>
                    </p>
                </div>
                
                <div class="_50">
                    <p>
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="0">Male</option>
                            <option value="1">Female</option>
                        </select>
                    </p>
                </div>
            </fieldset>
            
            <!-- Basic Information Field Set -->
            <fieldset>
                <legend>At Login Flags</legend>
                <div class="_100">
                    <p>
                        At Login flags are a list of different actions (Supported by this realm) taken once a player logs in with the character.
                        Enabling a flag will allow the player to use the flag to preform the specific action on this character.
                    </p>
                </div>
                {flags}
                    <div class="_50">
                        <p>
                            <label for="{label}">{name}</label>
                            <select id="{label}" name="{label}">
                                <option value="0" <?php if('{enabled}' == false) echo 'selected="selected"'; ?>>Disabled</option>
                                <option value="1" <?php if('{enabled}' == true) echo 'selected="selected"'; ?>>Enabled</option>
                            </select>
                        </p>
                    </div>
                {/flags}
            </fieldset>
            
            <!-- Submit buttons -->
            <div class="block-actions">
                <ul class="actions-left">
                    <li><a class="button red" id="reset-profile" href="javascript:void(0);">Undo Changes</a></li>
                </ul>
                <ul class="actions-right">
                    <li><input type="submit" class="button" value="Apply Changes"></li>
                </ul>
            </div>
        </form>
    </div>
</div>

<div class="grid_4">
    <div class="block-border">
        <div class="block-header">
            <h1>Character Information</h1><span></span>
        </div>
        <div class="block-content">
            <table id="data-table" class="table">
                <tbody>
                    <tr>
                        <td>Character Name:</td>
                        <td id="account_status">{character.name}</td>
                    </tr>
                    <tr>
                        <td>Race:</td>
                        <td>{race}</td>
                    </tr>
                    <tr>
                        <td>Class:</td>
                        <td>{class}</td>
                    </tr>
                    <tr>
                        <td>Zone:</td>
                        <td>{zone}</td>
                    </tr>
                    <tr>
                        <td>Account:</td>
                        <td>{account}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid_4">
    <div class="block-border">
        <div class="block-header">
            <h1>Actions</h1><span></span>
        </div>
        <div class="block-content">
            <div id="js_message" style="display: none;"></div>
            <center>
                <p>
                    <a id="unstuck" href="javascript:void(0);" class="button" style="width: 150px; text-align: center;">Unstuck Character</a>
                </p>
                <p>
                    <a href="{SITE_URL}/admin/users/{account}" class="button" style="width: 150px; text-align: center;">View Character Account</a>
                </p>
                <p>
                    <a id="delete" href="javascript:void(0);" class="button red" style="width: 150px; text-align: center;">Delete Character</a>
                </p>
            </center>
        </div>
    </div>
</div>