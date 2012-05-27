<form method="POST" action="index.php?step=2" class="form label-inline">
<div class="main-content">		
	<p>
		Welcome to the Plexis Installer!. Before we start the installation proccess, we need to make sure your
		web server is compatible with the cms. Please select your realms emulator, and click the start at the bottom to begin.
	</p>
	
	<div class="field" >
		<label for="user">Emulator: </label>
		<select name="emulator">
			<option value="trinity" select="selected">Trinity</option>
			<option value="mangos" select="selected">Mangos</option>
			<option value="arcemu">ArcEmu</option>
		</select>
		<p class="field_help">Please select your emulator.</p>
	</div>

	<div class="buttonrow-border">								
		<center><button><span>Start</span></button></center>			
	</div>
	<div class="clear"></div>
</div> <!-- .main-content -->
</form>