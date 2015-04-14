<?php
error_reporting(E_ALL);
//ini_set('display_errors', 0);

header("Cache-Control: no-store, no-cache, max-age=0");
header("Expires: -1");
?><!DOCTYPE html>
<?php
//declare variables (just for my sanity)
$errMsg = '';
$instance;
$dbPath; $db; $cmd; $sql; $rst;
$_;	//placeholder variable (need a variable to pass to $cmd->Execute(), but I don't care what gets put into it)
$settingsJson; $settingsArr;
$listType; $listLabels; $listPatterns;


if(empty($_GET['instance']) || !intval($_GET['instance'])){
	$errMsg = 'Instance number is not specified.';
}
else{
	
	$instance = intval($_GET['instance']);
	
	$dbPath = realpath($_SERVER['DOCUMENT_ROOT'].'/../data/overlayConfig.accdb');
	if(!file_exists($dbPath)){
		$errMsg = 'Could not find the database file.';
	}
	else{
		
		$db = new COM('ADODB.Connection');
		$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
		
		//make sure the instance number corresponds to an instance of this template
		$cmd = new COM('ADODB.Command');
		$cmd->ActiveConnection = $db;
		$cmd->CommandText = 'SELECT * FROM Instance INNER JOIN Template ON Instance.Template = Template.ID ' .
							'WHERE Template.Config = ? AND Instance.ID = ?';
		$cmd->CommandType = 1;	//adCmdText
		$rst = $cmd->Execute($_, array($_SERVER['URL'], $instance));
		
		
		if($rst->EOF){
			$errMsg = 'Specified instance does not use this template.';
		}
		else{
			$settingsJson = $rst['Settings']->Value;
			$settingsArr = json_decode($settingsJson, true);
		}
		
		$rst->Close();
		$db->Close();
		
	}
	
}
?>
<html>
<head>
	
	<meta charset="UTF-8">
	
	<title>Epic Stream Man's YouTube Player Configuration</title>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	
	<script type="text/javascript">
		var instance = <?php echo $instance; ?>,
			settings = <?php echo $settingsJson; ?>;
	</script>
	
</head>
<body>
<?php
if($errMsg){
	echo $errMsg;
}
else{
	$listType = $settingsArr['listType'];
	$listLabels = array('playlist'=>'Playlist ID', 'video_list'=>'Video IDs (comma-separated)', 'user_uploads'=>'User Name', 'search'=>'Search Query');
	$listPatterns = array('playlist'=>"[0-9a-zA-Z-]+", 'video_list'=>"[0-9a-zA-Z-]+(,\s*[0-9a-zA-Z-]+)*", 'user_uploads'=>"[0-9a-zA-Z_'-]*(\.[0-9a-zA-Z_'-]+)*\.?", 'search'=>"");
?>
	<p>
	<label>List Type <select id="listType">
		<option value="playlist" <?php echo empty($listType) || $listType == 'playlist' ? 'selected' : ''; ?>>playlist</option>
		<option value="video_list" <?php echo $listType == 'video_list' ? 'selected' : ''; ?>>list of videos</option>
		<option value="user_uploads" <?php echo $listType == 'user_uploads' ? 'selected' : ''; ?>>user uploads</option>
		<option value="search" <?php echo $listType == 'search' ? 'selected' : ''; ?>>search results</option>
	</select></label>
	</p>
	
	<p>
	<label><span id="listLabel"><?php echo $listLabels[$listType]; ?></span> <input type="text" id="list" pattern="<?php echo $listPatterns[$listType]; ?>" value="<?php echo $settingsArr['list']; ?>"></label>
	</p>
	
	<p>
	<label><input type="checkbox" id="shuffle" <?php echo $settingsArr['shuffle'] ? 'checked' : ''; ?>> Shuffle</label><br>
	<label><input type="checkbox" id="loop" <?php echo $settingsArr['loop'] ? 'checked' : ''; ?>> Loop</label>
	</p>
	
	<p>
	<label>Initial Volume <input type="range" id="volume" max="100" min="0" step="1" value="<?php echo $settingsArr['volume']; ?>"> <span id="volumeNum"><?php echo $settingsArr['volume']; ?></span></label>
	</p>
	
	<p>
	<input type="submit" id="save" value="Save" disabled> <input type="button" id="cancel" value="Cancel">
	</p>
	
	<form id="saveChanges" action="/set.php" method="post">
		<input type="hidden" name="instance" value="<?php echo $instance; ?>">
		<input type="hidden" id="settings" name="settings" value="">
	</form>
	
	<script type="text/javascript">
		var i_listType, i_list, i_shuffle, i_loop, i_volume, btn_save, btn_cancel, form, i_settings,
			listType = settings.listType,
			listValue = { playlist:"", video_list:"", user_uploads:"", search:"" },
			listLabels = { playlist:"Playlist ID", video_list:"Video IDs (comma-separated)", user_uploads:"User Name", search:"Search Query" },
			listLabel = document.getElementById("listLabel"),
			listPatterns = { playlist:"\s*[0-9a-zA-Z-]+\s*", video_list:"\s*[0-9a-zA-Z-]+(,\s*[0-9a-zA-Z-]+)*\s*", user_uploads:"\s*[0-9a-zA-Z_'-]*(\.[0-9a-zA-Z_'-]+)*\.?\s*", search:"" };
		
		listValue[listType] = settings.list;
		
		(i_listType = document.getElementById("listType")).addEventListener("change", listTypeChange, false);
		(i_list = document.getElementById("list")).addEventListener("change", updateSaveBtn, false);
		(i_shuffle = document.getElementById("shuffle")).addEventListener("change", updateSaveBtn, false);
		(i_loop = document.getElementById("loop")).addEventListener("change", updateSaveBtn, false);
		(i_volume = document.getElementById("volume")).addEventListener("input", volumeChange, false);
		i_volume.addEventListener("change", volumeChange, false);	//for IE
		(btn_save = document.getElementById("save")).addEventListener("click", save, false);
		btn_cancel = document.getElementById("cancel");
		form = document.getElementById("saveChanges");
		i_settings = document.getElementById("settings");
		document.getElementById("cancel").addEventListener("click", cancel, false);
		
		function updateSaveBtn(){
			
			if(i_listType.value == settings.listType && i_list.value == settings.list
			 && i_shuffle.checked == settings.shuffle && i_loop.checked == settings.loop
			 && i_volume.value == settings.volume){
				//all settings are the same as they were when the page loaded
				btn_save.disabled = true;
			}
			else if(!i_list.validity.valid){
				//list text box has an invalid value
				btn_save.disabled = true;
			}
			else{
				//one or more changes have been made
				btn_save.disabled = false;
			}
		}
		
		function listTypeChange(){
			//remember previous #list value
			listValue[listType] = i_list.value;
			listType = i_listType.value;
			
			//update #list input
			listLabel.innerHTML = listLabels[listType];
			i_list.value = listValue[listType];
			i_list.pattern = listPatterns[listType];
			
			updateSaveBtn();
		}
		
		function volumeChange(){
			document.getElementById("volumeNum").innerHTML = i_volume.value;
			
			updateSaveBtn();
		}
		
		function save(){
			var newSettings = {
					listType: i_listType.value,
					list: i_list.value.trim(),
					shuffle: i_shuffle.checked,
					loop: i_loop.checked,
					volume: 1*i_volume.value || settings.volume
				};
			
			if(newSettings.listType == "video_list"){
				newSettings.list = newSettings.list.replace(/\s+/g, "");
			}
			
			i_settings.value = JSON.stringify(newSettings);
			
			//disable the form fields
			btn_save.disabled = true;
			i_listType.disabled = i_list.disabled = i_shuffle.disabled = i_loop.disabled = i_volume.disabled, btn_cancel.disabled = true;
			//TODO: display some "waiting" indicator
			
			/*//doesn't work; page won't reload on a 205 status code (in Chrome, anyway)
			form.submit();
			*/
			/*
			//use jQuery to post the changes
			$(form).submit(function(){
				$.post($(this).attr('action'), $(this).serialize(), function(response){
					//on success, reload the page
					location.reload(true);
				},'json');
				return false;
			});
			*/
			//use jQuery to post the changes
			$.ajax({
				url: '/set.php',
				method: 'POST',
				data: {
					instance: instance,
					settings: JSON.stringify(newSettings)
				}
			}).done(function(content, message, xhr) {
				
				if (205 !== xhr.status) {	//error returned
					
					//display the error message
					alert("Failed to save settings:\n\n"+content);
					
					//re-enable the form fields
					i_listType.disabled = i_list.disabled = i_shuffle.disabled = i_loop.disabled = i_volume.disabled, btn_cancel.disabled = false;
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
			location.reload(true);
		}
	</script>
<?php
}
?>
</body>
</html>
