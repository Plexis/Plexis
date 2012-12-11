<div class="grid_8">
    <div class="block-border">
        <div class="block-header">
            <h1>Edit Users Account</h1>
        </div>
        
        <!-- Profile Form -->
        <form id="profile" class="block-content form" action="{SITE_URL}/admin_ajax/accounts" method="POST">
            <input type="hidden" name="action" value="update-account">
            <input type="hidden" name="id" value="{user.id}" />
            
            <!-- JS Ajax message for profile updates -->
            <div id="js_profile_message" style="display: none;"></div>
            
            <!-- Basic Information Field Set -->
            <fieldset>
                <legend>Basic Information</legend>
                <div class="_50">
                    <p>
                        <label for="username">Username</label>
                        <input id="username" disabled="disabled" type="text" value="{user.username}"/>
                    </p>
                </div>
                
                <div class="_50">
                    <p>
                        <label for="email">Email</label>
                        <input class="required" id="email" name="email" type="text" autocomplete="off" value="{user.email}"/>
                    </p>
                </div>
            </fieldset>
            
            <!-- Password Field Set -->
            <fieldset>
                <legend>Change Password</legend>
                <div class="_50">
                    <p>
                        <label for="password1">New Password</label>
                        <input id="password1" name="password1" type="password" value=""/>
                    </p>
                </div>
                
                <div class="_50">
                    <p>
                        <label for="password2">Repeat Password</label>
                        <input id="password2" name="password2" type="password" value=""/>
                    </p>
                </div>
            </fieldset>
            
            <!-- Site Settings Field Set -->
            <fieldset>
                <legend>Account Settings</legend>
                <div class="_50">
                    <p>
                        <label for="account level">User Group</label>
                        <select id="account level" name="group_id">
                            <?php
                                $ug = "{user.group_id}";
                                foreach($groups as $group)
                                {
                                    if($ug == $group['group_id'])
                                    {
                                        echo '<option value="'.$group['group_id'].'" selected="selected">'.$group['title'].'</option>';
                                    }
                                    else
                                    {
                                        echo '<option value="'.$group['group_id'].'">'.$group['title'].'</option>';
                                    }
                                }
                            ?>
                        </select>
                    </p>
				</div>
				<div class="_50">
					<p>
						<label for="expansion">Expansion</label>
						<select id="expansion" name="expansion">
							<?php
								$current_expansion = intval("{user.expansion}");
								
								foreach( $expansion_data as $id => $name )
								{
									if( $id == $current_expansion )
										print("<option value=\"$id\" selected=\"selected\">$name</option>");
									else
										print("<option value=\"$id\">$name</option>");
								}
							?>
						</select>
					</p>
                </div>
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
            <h1>Account Information</h1><span></span>
        </div>
        <div class="block-content">
            <table id="data-table" class="table">
                <tbody>
                    <tr>
                        <td>Account Status:</td>
                        <td id="account_status">{user.status}</td>
                    </tr>
                    <tr>
                        <td>Registration Date:</td>
                        <td>{user.joindate}</td>
                    </tr>
                    <tr>
                        <td>Registration IP:</td>
                        <td>{user.registration_ip}</td>
                    </tr>
                    <tr>
                        <td>Last Activity (In-Game):</td>
                        <td>{user.last_login}</td>
                    </tr>
                    <tr>
                        <td>Last Activity (Site):</td>
                        <td>{user.last_seen}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid_4">
    <div class="block-border">
        <div class="block-header">
            <h1>Account Actions</h1><span></span>
        </div>
        <div class="block-content">
            <div id="js_message" style="display: none;"></div>
            <center>
                <p>
                    <a id="account-ban-button" name="{account_ban_button}" href="javascript:void(0);" class="button" style="width: 150px; text-align: center;">{account_ban_button_text}</a>
                </p>
                <p>
                    <a id="account-lock-button" name="{account_lock_button}" href="javascript:void(0);" class="button" style="width: 150px; text-align: center;">{account_lock_button_text}</a>
                </p>
                <p><a id="account-delete-button" href="javascript:void(0);" class="red button" style="width: 150px; text-align: center;" >Delete Account</a></p>
            </center>
        </div>
    </div>
</div>

<!-- hidden ban form -->
<div id="ban-form" title="Ban User" style="display: none;">
    <div id="js_ban_message" style="display: none"></div>
    <form id="ban" class="form" action="{SITE_URL}/admin_ajax/accounts" method="POST">
        <input type="hidden" name="action" value="ban-account" />
        <input type="hidden" name="id" value="{user.id}" />
        <div>
            <p>Submitting this form will ban {user.username} until the date specified.</p>
            <p>
                <label for="username">Account</label>
                <input id="username" name="username" disabled="disabled" type="text" value="{user.username}" />
            </p>
            <p>
                <label for="banreason">Ban Reason</label>
                <textarea id="banreason" name="banreason" class="required" rows="5" cols="40"></textarea>
            </p>
            <p>
                <label for="unbandate">UnBan Date</label>
                <input id="unbandate" name="unbandate" class="required" type="text" value="" />
            </p>
            <p>
				<label><input type="checkbox" name="banip" value="1"/>Ban Accounts Last Known Ip? (Note: IP bans must be manually unbanned)</label>
            </p>
            <p>
                <input id="submit" type="submit" class="button" style="width: 150px; text-align: center; margin: 10px; float: right;" value="Submit">
            </p>
        </div>
    </form>
</div>