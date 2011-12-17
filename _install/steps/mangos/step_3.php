<!-- STEP 3 -->
<form method="POST" action="index.php?step=4" class="form label-inline">
<input type="hidden" name="emulator" value="<?php echo $_POST['emulator']; ?>" />
<div class="main-content">		
    <p>In this next step, we need you to enter your Plexis database information.</p>
    <div class="field">
        <label for="db user">Plexis Database Host: </label>
        <input id="Site Title" name="db_host" size="20" type="text" class="medium" value="localhost" />
        <p class="field_help">Enter you database host.</p>
    </div>
    
    <div class="field">
        <label for="db user">Plexis Database port: </label>
        <input id="Site Title" name="db_port" size="20" type="text" class="medium" value="3306" />
        <p class="field_help">Enter the port number of your database.</p>
    </div>
    
    <div class="field">
        <label for="db user">Plexis Database User: </label>
        <input id="Site Title" name="db_username" size="20" type="text" class="medium" value="root" />
        <p class="field_help">Enter you database username.</p>
    </div>
    
    <div class="field">
        <label for="db user">Plexis Database Pass: </label>
        <input id="Site Title" name="db_password" size="20" type="password" class="medium" value="ascent"/>
        <p class="field_help">Enter you database Password.</p>
    </div>
    
    <div class="field">
        <label for="db user">Plexis Database: </label>
        <input id="Site Title" name="db_name" size="20" type="text" class="medium" value="plexis" />
        <p class="field_help">Enter your Plexis database name.</p>
    </div>
    
    <!-- -->
    -------------------------------------------------------------------------------------------------------------
    <br />
    
    <div class="field">
        <label for="db user">Realm Database Host: </label>
        <input id="Site Title" name="rdb_host" size="20" type="text" class="medium" value="localhost" />
        <p class="field_help">Enter you database host.</p>
    </div>
    
    <div class="field">
        <label for="db user">Realm Database port: </label>
        <input id="Site Title" name="rdb_port" size="20" type="text" class="medium" value="3306" />
        <p class="field_help">Enter the port number of your database.</p>
    </div>
    
    <div class="field">
        <label for="db user">Realm Database User: </label>
        <input id="Site Title" name="rdb_username" size="20" type="text" class="medium" value="root" />
        <p class="field_help">Enter you database username.</p>
    </div>
    
    <div class="field">
        <label for="db user">Realm Database Pass: </label>
        <input id="Site Title" name="rdb_password" size="20" type="password" class="medium" value="ascent"/>
        <p class="field_help">Enter you database Password.</p>
    </div>
    
    <div class="field">
        <label for="db user">Realm Database: </label>
        <input id="Site Title" name="rdb_name" size="20" type="text" class="medium" value="realmd" />
        <p class="field_help">Enter your Realm database name.</p>
    </div>
    
    <div class="buttonrow-border">								
        <center><button><span>Install Database</span></button></center><br />
        <center><button name="skip" class="btn-sec"><span>Skip Database Install</span></button></center>							
    </div>
    <div class="clear"></div>
</div> <!-- .main-content -->
</form>		