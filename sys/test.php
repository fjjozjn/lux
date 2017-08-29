<?php
session_start();
?>
<style>
	.popup .text_popup {
	visibility: hidden;
	}
	.popup .show{
	visibility: visible
	}
	</style>
	
	<form action = "save.php" method="post">
	<p> Input Text: <input type="text" name="text" size="10" maxlength="20" /> </p>
	<p><input type="submit" name="save1" value="Save" /></p>
	<br>
	
	</form>
	
	<div class="popup"
	<button type="button" onclick="myFunction()"> Show Text
	</button>
	<span class="text_popup" 
	id="popup_window"> 
	<br>
	<?php 
	
	echo "<p>Here is the text: "  {$_SESSION['saved_text']}  "</p>";

	?>
	</span>
	</div>
	
	
	
	<script>
	function myFunction() {
	var popup = document.getElementById("popup_window");
	popup.classList.toggle("show");
	}
	</script>
	