<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/
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
$listType; $listLabels;


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
							'WHERE Template.Path = ? AND Instance.ID = ?';
		$cmd->CommandType = 1;	//adCmdText
		$rst = $cmd->Execute($_, array($_SERVER['URL'], $instance));
		
		
		if($rst->EOF){
			$errMsg = 'Specified instance does not use this template.';
		}
		else{
			$settingsJson = $rst['Settings'];
			$settingsArr = json_decode($settings, true);
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
	<label><span id="listLabel"><?php echo $listLabels[$listType]; ?></span> <input type="text" id="list" value="<?php echo $settingsArr['list']; ?>"></label>
	</p>
	
	<p>
	<label><input type="checkbox" id="shuffle" <?php echo $settingsArr['shuffle'] ? 'checked' : ''; ?>> Shuffle</label><br>
	<label><input type="checkbox" id="loop" <?php echo $settingsArr['loop'] ? 'checked' : ''; ?>> Loop</label>
	</p>
	
	<p>
	<label>Volume <input type="number" id="volume" max="100" min="0" step="1" value="<?php echo $settingsArr['volume']; ?>">%</label>
	</p>
	
	<p>
	<input type="submit" id="save" value="Save" disabled> <input type="button" id="cancel" value="Cancel">
	</p>
	
	<iframe id="saveChanges" style="display:none;"></iframe>
	
	<script type="text/javascript">
		var i_listType, i_list, i_shuffle, i_loop, i_volume, btn_save,
			listType = settings.listType,
			listValue = { playlist:"", video_list:"", user_uploads:"", search:"" },
			listLabels = { playlist:"Playlist ID", video_list:"Video IDs (comma-separated)", user_uploads:"User Name", search:"Search Query" },
			listLabel = document.getElementById("listLabel");
		
		listValue[listType] = settings.list;
		
		(i_listType = document.getElementById("listType")).addEventListener("change", listTypeChange, false);
		(i_list = document.getElementById("list")).addEventListener("change", updateSaveBtn, false);
		(i_shuffle = document.getElementById("shuffle")).addEventListener("change", updateSaveBtn, false);
		(i_loop = document.getElementById("loop")).addEventListener("change", updateSaveBtn, false);
		(i_volume = document.getElementById("volume")).addEventListener("change", updateSaveBtn, false);
		(btn_save = document.getElementById("save")).addEventListener("click", save, false);
		document.getElementById("cancel").addEventListener("click", cancel, false);
		
		function updateSaveBtn(evt){
			if(i_listType.value == settings.listType && i_list.value == settings.list
			 && i_shuffle.checked == settings.shuffle && i_loop.checked == settings.loop
			 && i_volume.value == settings.volume){
				//all settings are the same as they were when the page loaded
				btn_save.disabled = true;
			}
			else{
				//one or more changes have been made
				btn_save.disabled = false;
			}
		}
		
		function listTypeChange(evt){
			//remember previous #list value
			listValue[listType] = i_list.value;
			listType = i_listType.value;
			
			//update #list input
			listLabel.innerHTML = listLabels[listType];
			i_list.value = listValue[listType];
		}
		
		function save(evt){
			var newSettings = JSON.stringify({
					listType: i_listType.value,
					list: i_list.value,
					shuffle: i_shuffle.checked,
					loop: i_loop.checked,
					volume: i_volume.value
				}),
				iframe = document.getElementById("saveChanges");
			
			iframe.contentWindow.document.innerHTML = '<html><body><form action="set.php" method="post">' +
				'<input type="hidden" name="instance" value="'+instance+'">' +
				'<input type="hidden" name="settings" value="'+newSettings+'">' +
				'</form></body></html>';
			iframe.contentWindow.document.getElementById("set").submit();
		}
		
		function cancel(evt){
			location.reload(true);
		}
	</script>
<?php
}
?>
</body>
</html>
