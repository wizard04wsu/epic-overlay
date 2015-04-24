<?php
error_reporting(E_ALL);
//ini_set('display_errors', 0);

header("Cache-Control: no-store, no-cache, max-age=0");
header("Expires: -1");

if(@$_POST['getDefaults']){
	//respond with the default settings for this template
	exit('{"listType":"playlist","list":"","shuffle":"yes","loop":"yes","volume":"100"}');
}

require '_getSettings_cfg.php';

$listType; $listLabels; $listPatterns;

?><!DOCTYPE html>

<html>
<head>
	
	<meta charset="UTF-8">
	
	<title>Epic Overlay YouTube player configuration</title>
	
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
	$listType = $settingsArr['listType'];
	$listLabels = array('playlist'=>'Playlist ID', 'video_list'=>'Video IDs (comma-separated)', 'user_uploads'=>'User Name', 'search'=>'Search Query');
	$listPatterns = array('playlist'=>" *[0-9a-zA-Z_-]+ *", 'video_list'=>" *[0-9a-zA-Z_-]+(, *[0-9a-zA-Z_-]+)* *", 'user_uploads'=>" *[0-9a-zA-Z_'-]*(\.[0-9a-zA-Z_'-]+)*\.? *", 'search'=>"");
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
			<div><label for="listType">List Type</label></div>
			<div>
				<select id="listType">
					<option value="playlist" <?php echo empty($listType) || $listType == 'playlist' ? 'selected' : ''; ?>>playlist</option>
					<option value="video_list" <?php echo $listType == 'video_list' ? 'selected' : ''; ?>>list of videos</option>
					<option value="user_uploads" <?php echo $listType == 'user_uploads' ? 'selected' : ''; ?>>user uploads</option>
					<option value="search" <?php echo $listType == 'search' ? 'selected' : ''; ?>>search results</option>
				</select>
			</div>
		</div>
		<div>
			<div><label for="list"><span id="listLabel"><?php echo $listLabels[$listType]; ?></span></label></div>
			<div><input type="text" id="list" <?php echo $listPatterns[$listType] ? 'pattern="'.$listPatterns[$listType].'"' : ''?> value="<?php echo $settingsArr['list']; ?>"></div>
		</div>
	</div>
	
	<p>
	<label><input type="checkbox" id="shuffle" <?php echo $settingsArr['shuffle'] ? 'checked' : ''; ?>> Shuffle</label><br>
	<label><input type="checkbox" id="loop" <?php echo $settingsArr['loop'] ? 'checked' : ''; ?>> Loop</label>
	</p>
	
	<p>
	<label>Initial Volume <input type="range" id="volume" max="100" min="0" step="1" value="<?php echo $settingsArr['volume']; ?>"> <span id="volumeNum"><?php echo $settingsArr['volume']; ?></span>%</label>
	</p>
	
	<p style="margin-bottom:0;">
	<!-- save button must have id="save" so overlayConfig.php can find it -->
	<input type="submit" id="save" value="Save" disabled> <input type="button" id="cancel" value="Cancel">
	</p>
	
	<script type="text/javascript">
		var i_title, i_listType, i_list, i_shuffle, i_loop, i_volume, btn_save, btn_cancel,
			listType = settings.listType,
			listValue = { playlist:"", video_list:"", user_uploads:"", search:"" },
			listLabels = { playlist:"Playlist ID", video_list:"Video IDs (comma-separated)", user_uploads:"User Name", search:"Search Query" },
			listLabel = document.getElementById("listLabel"),
			listPatterns = { playlist:" *[0-9a-zA-Z_-]+ *", video_list:" *[0-9a-zA-Z_-]+(, *[0-9a-zA-Z_-]+)* *", user_uploads:" *[0-9a-zA-Z_'-]*(\.[0-9a-zA-Z_'-]+)*\.? *", search:"" };
		
		listValue[listType] = settings.list;
		
		(i_title = document.getElementById("title")).addEventListener("input", updateSaveBtn, false);
		i_title.addEventListener("change", updateSaveBtn, false);	//for IE
		(i_listType = document.getElementById("listType")).addEventListener("change", listTypeChange, false);
		(i_list = document.getElementById("list")).addEventListener("input", updateSaveBtn, false);
		i_list.addEventListener("change", updateSaveBtn, false);	//for IE
		(i_shuffle = document.getElementById("shuffle")).addEventListener("change", updateSaveBtn, false);
		(i_loop = document.getElementById("loop")).addEventListener("change", updateSaveBtn, false);
		(i_volume = document.getElementById("volume")).addEventListener("input", volumeChange, false);
		i_volume.addEventListener("change", volumeChange, false);	//for IE
		(btn_save = document.getElementById("save")).addEventListener("click", save, false);
		(btn_cancel = document.getElementById("cancel")).addEventListener("click", cancel, false);;
		
		function updateSaveBtn(){
			
			if(i_title.value == HTMLToText(instanceTitle) && i_listType.value == settings.listType && i_list.value == settings.list
			 && i_shuffle.checked == settings.shuffle && i_loop.checked == settings.loop
			 && i_volume.value == settings.volume){
				//all settings are the same as they were when the page loaded
				btn_save.disabled = true;
			}
			else if(!i_title.validity.valid || !i_list.validity.valid){
				//list text box has an invalid value
				btn_save.disabled = true;
			}
			else{
				//one or more changes have been made
				btn_save.disabled = false;
			}
			console.log(btn_save.disabled);
		}
		
		function listTypeChange(){
			//remember previous #list value
			listValue[listType] = i_list.value;
			listType = i_listType.value;
			
			//update #list input
			listLabel.innerHTML = listLabels[listType];
			i_list.value = listValue[listType];
			if(listPatterns[listType]){
				i_list.pattern = listPatterns[listType];
			}
			else{
				i_list.removeAttribute("pattern");
			}
			
			updateSaveBtn();
		}
		
		function volumeChange(){
			document.getElementById("volumeNum").innerHTML = i_volume.value;
			
			updateSaveBtn();
		}
		
		function save(){
			var newSettings, volume;
			
			volume = 1*i_volume.value;
			if(!volume && volume !== 0) volume = settings.volume;
			
			var newSettings = {
					listType: i_listType.value,
					list: i_list.value.trim(),
					shuffle: i_shuffle.checked,
					loop: i_loop.checked,
					volume: volume
				};
			
			if(newSettings.listType == "video_list"){
				newSettings.list = newSettings.list.replace(/\s+/g, "");
			}
			
			//disable the form fields
			btn_save.disabled = true;
			i_title.disabled = i_listType.disabled = i_list.disabled = i_shuffle.disabled = i_loop.disabled = i_volume.disabled, btn_cancel.disabled = true;
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
					i_title.disabled = i_listType.disabled = i_list.disabled = i_shuffle.disabled = i_loop.disabled = i_volume.disabled, btn_cancel.disabled = false;
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
			i_title.value = HTMLToText(instanceTitle);
			listValue = { playlist:"", video_list:"", user_uploads:"", search:"" }
			i_list.value = "";
			i_listType.value = settings.listType;
			listTypeChange();
			i_list.value = settings.list;
			i_shuffle.checked = settings.shuffle;
			i_loop.checked = settings.loop;
			i_volume.value = settings.volume;
			document.getElementById("volumeNum").innerHTML = i_volume.value;
			
			updateSaveBtn();
		}
	</script>
<?php
}
?>
</body>
</html>
