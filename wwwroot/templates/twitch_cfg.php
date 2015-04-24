<?php
error_reporting(E_ALL);
//ini_set('display_errors', 0);

header("Cache-Control: no-store, no-cache, max-age=0");
header("Expires: -1");

if(@$_POST['getDefaults']){
	//respond with the default settings for this template
	exit('{"channel":"","video":"","volume":"100"}');
}

require '_getSettings_cfg.php';

$volume;

if(!$errMsg){
	
	$volume = intval($settingsArr['volume']);
	$volume = $volume < 0 ? 0 : $volume > 100 ? 100 : $volume;
	
}

?><!DOCTYPE html>

<html>
<head>
	
	<meta charset="UTF-8">
	
	<title>Epic Overlay: Twitch player configuration</title>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script type="text/javascript" src="inc/htmlEncode.js"></script>
	
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
		<div>
			<div><label for="url">URL</label></div>
			<div><input type="url" id="url" value=""></div>
		</div>
		<div>
			<div><label for="channel">Channel</label></div>
			<div><input type="text" id="channel" pattern=" *[a-zA-Z_]+ *" value="<?php echo $settingsArr['channel']; ?>"></div>
		</div>
	<!--</div>
	<p>
	To play a specific video instead of the live stream, supply the video ID. This can be found by 
	</p>
	<div class="fillWidth">-->
		<div>
			<div><label for="video">Video ID</label></div>
			<div><input type="text" id="video" pattern=" *[a-zA-Z0-9]* *" value="<?php echo $settingsArr['video']; ?>"></div>
		</div>
	</div>
	
	<p>
	<label>Initial Volume <input type="range" id="volume" max="100" min="0" step="1" value="<?php echo $volume; ?>"> <span id="volumeNum"><?php echo $volume; ?></span></label>
	</p>
	
	<p style="margin-bottom:0;">
	<!-- save button must have id="save" so overlayConfig.php can find it -->
	<input type="submit" id="save" value="Save" disabled> <input type="button" id="cancel" value="Cancel">
	</p>
	
	<script type="text/javascript">
		var i_title, i_url, i_channel, i_video, i_volume, btn_save, btn_cancel;
		
		(i_title = document.getElementById("title")).addEventListener("input", updateSaveBtn, false);
		i_title.addEventListener("change", updateSaveBtn, false);	//for IE
		(i_url = document.getElementById("url")).addEventListener("input", populateIDs, false);
		i_url.addEventListener("change", populateIDs, false);	//for IE
		(i_channel = document.getElementById("channel")).addEventListener("input", updateSaveBtn, false);
		i_channel.addEventListener("change", updateSaveBtn, false);	//for IE
		(i_video = document.getElementById("video")).addEventListener("input", updateSaveBtn, false);
		i_video.addEventListener("change", updateSaveBtn, false);	//for IE
		(i_volume = document.getElementById("volume")).addEventListener("input", volumeChange, false);
		i_volume.addEventListener("change", volumeChange, false);	//for IE
		(btn_save = document.getElementById("save")).addEventListener("click", save, false);
		(btn_cancel = document.getElementById("cancel")).addEventListener("click", cancel, false);;
		
		function populateIDs(){
			
			var rxp = /^(?:https?:\/\/)?(?:www\.)?twitch\.tv\/([a-z0-9_]+)(?:\/([cv])\/(\d+))?\/?$/i,
				m;
			
			if(i_url.value){
				i_channel.disabled = i_video.disabled = true;
				
				m = rxp.exec(i_url.value);
				if(m){
					i_channel.value = m[1];
					i_video.value = m[2]+m[3] || "";
				}
				else{
					i_channel.value = "";
					i_video.value = "";
				}
			}
			else{
				i_channel.value = settings.channel;
				i_video.value = settings.video;
				
				i_channel.disabled = i_video.disabled = false;
			}
			
			updateSaveBtn();
			
		}
		
		function updateSaveBtn(){
			
			if(i_title.value == instanceTitle && i_channel.value == settings.channel && i_video.value == settings.video && i_volume.value == settings.volume){
				//all settings are the same as they were when the page loaded
				btn_save.disabled = true;
			}
			else if(!i_title.validity.valid || !i_channel.validity.valid || !i_video.validity.valid){
				//a text box has an invalid value
				btn_save.disabled = true;
			}
			else if(i_channel.disabled && i_channel.value == ""){
				//invalid URL
				btn_save.disabled = true;
			}
			else{
				//one or more changes have been made
				btn_save.disabled = false;
			}
		}
		
		function volumeChange(){
			document.getElementById("volumeNum").innerHTML = i_volume.value;
			
			updateSaveBtn();
		}
		
		function save(){
			var newSettings, volume;
			
			volume = 1*i_volume.value;
			if(!volume && volume !== 0) volume = settings.volume;
			
			newSettings = {
					channel: i_channel.value.trim(),
					video: i_video.value.trim(),
					volume: volume
				};
			
			//disable the form fields
			btn_save.disabled = true;
			i_title.disabled = i_channel.disabled = i_video.disabled = i_volume.disabled, btn_cancel.disabled = true;
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
					i_title.disabled = i_channel.disabled = i_video.disabled = i_volume.disabled, btn_cancel.disabled = false;
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
			//location.reload(true);
			i_title.value = instanceTitle;
			i_channel.value = settings.channel;
			i_video.value = settings.video;
			i_volume.value = settings.volume;
		}
	</script>
<?php
}
?>
</body>
</html>
