<!-- STEP 4 -->
<form method="POST" action="index.php?step=5" class="form label-inline">
    <input type="hidden" name="db_host" value="<?php echo $_POST['db_host']; ?>">
    <input type="hidden" name="db_port" value="<?php echo $_POST['db_port']; ?>">
    <input type="hidden" name="db_name" value="<?php echo $_POST['db_name']; ?>">
    <input type="hidden" name="db_username" value="<?php echo $_POST['db_username']; ?>">
    <input type="hidden" name="db_password" value="<?php echo $_POST['db_password']; ?>">
    <input type="hidden" name="rdb_host" value="<?php echo $_POST['rdb_host']; ?>">
    <input type="hidden" name="rdb_port" value="<?php echo $_POST['rdb_port']; ?>">
    <input type="hidden" name="rdb_name" value="<?php echo $_POST['rdb_name']; ?>">
    <input type="hidden" name="rdb_username" value="<?php echo $_POST['rdb_username']; ?>">
    <input type="hidden" name="rdb_password" value="<?php echo $_POST['rdb_password']; ?>">
    <input type="hidden" name="emulator" value="<?php echo $_POST['emulator']; ?>" />

    <div class="main-content">		
        <p>
            Please create an admin account. If you already have an account, then type in your info to log in, so 
            you can be added as a site admin.
        </p>
        
        <div class="field">
            <label for="user">Username: </label>
            <input id="user" name="account" size="20" type="text" class="medium"/>
        </div>
        
        <div class="field">
            <label for="pass">Password: </label>
            <input id="pass" name="pass" size="20" type="password" class="medium"/>
        </div>
        
        <div class="field">
            <label for="pass2">Repeat Password: </label>
            <input id="pass2" name="pass2" size="20" type="password" class="medium"/>
        </div>
        
        <div class="buttonrow-border">								
            <center><button><span>Submit</span></button></center>			
        </div>
        <div class="clear"></div>
    </div> <!-- .main-content -->
</form>