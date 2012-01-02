<?php if( $can_create_keys == 1 ): ?>
<div class="left-box">
	<h2>Invitation Keys</h2>
	<div class="left-box-content">
		<center>
			<table id="key-table" style="margin-top: -7px;">
			<?php if( isset($keys) ): ?>
				{keys}
				<tr id="key-{loop.count}">
					<td>{key}</td>
					<td><a href="{SITE_URL}/account/invite_keys/delete/{key}" class="right">Delete</a></td>
				</tr>
				{/keys}
			<?php else: ?>
				<p><div class="alert info">You do not have any registration keys!</div></p>
			<?php endif; ?>
			</table>
		</center>
		<hr />
		<a href="{SITE_URL}/account/invite_keys/create" class="button">Create New</a>
	</div>
</div>
<?php
else:
redirect( SITE_URL );
endif;
?>