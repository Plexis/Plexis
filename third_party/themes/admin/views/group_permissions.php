<div class="grid_12">
    <!-- Button -->
    <div>
        <a href="{SITE_URL}/admin/groups" class="button">Back to Groups</a>
    </div>

    <div class="block-border">
        <div class="block-header">
            <h1>{group.title} Permissions</h1>
        </div>
        <div class="block-content">
            <!-- Hidden Message -->
            <div id="js_message" style="display: none;"></div>
            
            <form id="permissions-form" class="form" action="{SITE_URL}/admin_ajax/permissions" method="post">
                <input type="hidden" name="action" value="save"/>
                <input type="hidden" name="id" value="{group.group_id}"/>
            
                <?php
                foreach($sections as $field)
                {
                    echo '
                    <fieldset>
                        <legend>'. ucfirst($field) .' Permissions</legend>
                        <br /><br />
                        <div class="_50">';
                            foreach($permissions[$field] as $key => $value)
                            {
                                echo '
                                    <p>
                                        <label for="'. $key .'">'. $list[$key]['name'] .'</label>
                                        <select id="'. $key .'" name="perm__'. $key .'" title="'. $list[$key]['description'] .'">
                                            <option value="0"'; if($value == 0) echo " selected=\"selected\""; echo '>Disallow</option>
                                            <option value="1"'; if($value == 1) echo " selected=\"selected\""; echo '>Allow</option>
                                        </select>
                                    </p>
                                </div>
                                <div class="_50">';
                            }
                        echo'
                        </div>
                    </fieldset>';
                }
                ?>

                <!-- Form Buttons -->
                <div class="block-actions">
                    <ul class="actions-left">
                        <li><a class="button red" href="{SITE_URL}/admin/groups/permissions/{group.group_id}">Undo Changes</a></li>
                    </ul>
                    <ul class="actions-right">
                        <li><input type="submit" class="button" value="Apply Changes"></li>
                    </ul>
                </div>
            </form>
        </div>
    </div>
</div>