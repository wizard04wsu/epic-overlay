<?php
error_reporting(E_ALL);
//ini_set('display_errors', 0);

header("Cache-Control: no-store, no-cache, max-age=0");
header("Expires: -1");

if(@$_POST['getDefaults']){
	//respond with the default settings for this template
	exit('{}');
}

require '_getSettings_cfg.php';

if(!$errMsg){
	
	//
	
}

?><!DOCTYPE html>

<html>
<head>
	
	<meta charset="UTF-8">
	
	<title>Epic Overlay: player configuration</title>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script type="text/javascript" src="../inc/htmlEncode.js"></script>
	
	<style type="text/css" media="all">
		body {
			padding:6px 8px 8px;
			margin:0;
		}
		
		div.fillWidth {
			display:table;
			width:100%;
		}
		div.fillWidth > div {
			display:table-row;
		}
		div.fillWidth > div > div {
			display:table-cell;
			padding:0.25em 0;
		}
		div.fillWidth label {
			white-space:nowrap;
			padding-right:0.5em;
		}
		div.fillWidth > div > div:last-child {
			width:100%;
		}
		
		div.fillWidth > div > div:last-child input {
			box-sizing:border-box;
			width:100%;
		}
	</style>
	
</head>
<body>
<?php
if($errMsg){
	echo $errMsg;
}
else{
?>
	<script type="text/javascript">
		/*** instanceTitle variable is required by overlayConfig.php ***/
		var instanceTitle = <?php echo json_encode($title); ?>;
		
		var instance = <?php echo $instance; ?>,
			settings = <?php echo $settingsJson; ?>;
	</script>
	
	<div class="fillWidth">
		<div>
			<div><label for="title">Title</label></div>
			<div><input type="text" id="title" pattern=".+" value="<?php echo $title; ?>"></div>
		</div>
	</div>
	
	<p style="margin-bottom:0;">
	<!-- save button must have id="save" so overlayConfig.php can find it -->
	<input type="submit" id="save" value="Save" disabled> <input type="button" id="cancel" value="Cancel">
	</p>
	
	<script type="text/javascript">
		var i_title, btn_save, btn_cancel;
		
		(i_title = document.getElementById("title")).addEventListener("input", updateSaveBtn, false);
		i_title.addEventListener("change", updateSaveBtn, false);	//for IE
		
		(btn_save = document.getElementById("save")).addEventListener("click", save, false);
		(btn_cancel = document.getElementById("cancel")).addEventListener("click", cancel, false);;
		
		function updateSaveBtn(){
			
			if(i_title.value == HTMLToText(instanceTitle)){
				//all settings are the same as they were when the page loaded
				btn_save.disabled = true;
			}
			else if(!i_title.validity.valid){
				//a text box has an invalid value
				btn_save.disabled = true;
			}
			else{
				//one or more changes have been made
				btn_save.disabled = false;
			}
		}
		
		function save(){
			var newSettings;
			
			newSettings = {};
			
			//disable the form fields
			btn_save.disabled = true;
			i_title.disabled = true;
			//TODO: display some "waiting" indicator
			
			
			//use jQuery to post the changes
			$.ajax({
				url: '../set.php',
				method: 'POST',
				data: {
					instance: instance,
					action: "saveInstance",
					title: textToHTML(i_title.value),
					settings: JSON.stringify(newSettings)
				}
			}).done(function(content, message, xhr) {
				
				if (205 !== xhr.status) {	//error returned
					
					//display the error message
					alert("Failed to save settings:\n\n"+content);
					
					//re-enable the form fields
					i_title.disabled = false;
					updateSaveBtn();
					
					return;
					
				}
				
				//success; reload the settings page
				window.location.reload(true)
				
			}).fail(function(xhr, message, errorThrown) {
				//display a generic error message
				alert("Failed to save settings:\n\n"+message+"\n\n"+errorThrown);
			})
		}
		
		function cancel(){
			i_title.value = HTMLToText(instanceTitle);
			
			updateSaveBtn();
		}
	</script>
<?php
}
?>
</body>
</html>
