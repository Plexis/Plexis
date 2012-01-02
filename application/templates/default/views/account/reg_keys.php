<div class="left-box">
	<h2>Invitation Keys</h2>
	<div class="left-box-content">
        <table id="key-table" style="margin-top: -7px;">
        <?php if( sizeof($keys) > 0 ): ?>
            {keys}
            <tr id="key-{loop.count}">
                <td>{key}</td>
                <td><a href="{SITE_URL}/account/invite_keys/delete/{id}" class="right">Delete</a></td>
            </tr>
            {/keys}
        <?php else: ?>
            <p><div class="alert info">You do not have any registration keys!</div></p>
        <?php endif; ?>
        </table>
		<hr />
		<a href="{SITE_URL}/account/invite_keys/create" class="button">Create New</a>
	</div>
</div>