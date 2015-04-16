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
/*$dbPath;*/ $db; $cmd; $sql; $rst;
$pathParts;
$_;	//placeholder variable (need a variable to pass to $cmd->Execute(), but I don't care what gets put into it)
$settingsJson; $settingsArr;
$listType; $listLabels; $listPatterns;


if(@$_POST['getDefaults']){
	//respond with the default settings for this template
	exit('{"listType":"playlist","list":"","shuffle":"yes","loop":"yes","volume":"100"}');
}


if(empty($_GET['instance']) || !intval($_GET['instance'])){
	$errMsg = 'Instance number is not specified.';
}
else{
	
	$instance = intval($_GET['instance']);
	
	//connect to the database
	require '../dbPath.php';
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
		$pathParts = explode('/', $_SERVER['URL']);
		$rst = $cmd->Execute($_, array($pathParts[count($pathParts)-1], $instance));
		
		
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
	
	<style type="text/css" media="all">
		body {
			padding:6px 8px;
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
	<div class="fillWidth">
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
	<label>Initial Volume <input type="range" id="volume" max="100" min="0" step="1" value="<?php echo $settingsArr['volume']; ?>"> <span id="volumeNum"><?php echo $settingsArr['volume']; ?></span></label>
	</p>
	
	<p>
	<input type="submit" id="save" value="Save" disabled> <input type="button" id="cancel" value="Cancel">
	</p>
	
	<script type="text/javascript">
		var i_listType, i_list, i_shuffle, i_loop, i_volume, btn_save, btn_cancel,
			listType = settings.listType,
			listValue = { playlist:"", video_list:"", user_uploads:"", search:"" },
			listLabels = { playlist:"Playlist ID", video_list:"Video IDs (comma-separated)", user_uploads:"User Name", search:"Search Query" },
			listLabel = document.getElementById("listLabel"),
			listPatterns = { playlist:" *[0-9a-zA-Z_-]+ *", video_list:" *[0-9a-zA-Z_-]+(, *[0-9a-zA-Z_-]+)* *", user_uploads:" *[0-9a-zA-Z_'-]*(\.[0-9a-zA-Z_'-]+)*\.? *", search:"" };
		
		listValue[listType] = settings.list;
		
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
			
			//disable the form fields
			btn_save.disabled = true;
			i_listType.disabled = i_list.disabled = i_shuffle.disabled = i_loop.disabled = i_volume.disabled, btn_cancel.disabled = true;
			//TODO: display some "waiting" indicator
			
			
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
