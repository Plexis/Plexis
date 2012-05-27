<div class="main-content">		
<p>
    <?php if($success) : ?>
	
        <div class="alert success">Congratulations! Plexis is installed and ready for use!</div>
		Please login and visit the admin panel to further configure the site!
        <br /><a href="../index.php">Click Here</a> To go to your Plexis home page.
		
    <?php elseif($mode == 0): ?>
	
		<div class="alert error">There was an error creating your new admin account in the Realm account table.</div>
		
	<?php else: ?>
		
		<div class="alert warning">
			Plexis installed successfully, but there was an error adding your account into the cms accounts table.
			Try logging into the cms. If you are uable to login, Please contact a Plexis developer, and describe this
			error you are recieveing.
		</div>
		
	<?php endif; ?>
</p>
</div>