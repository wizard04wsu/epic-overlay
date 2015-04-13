<!DOCTYPE html>
<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/

//declare variables (just for my sanity)
$errMsg = '';
$instance = null;
$dbPath = '';
$db = null;
$cmd = null;
$_;	//placeholder variable (need a variable to pass to $cmd->Execute(), but I don't care what gets put into it)
$sql = '';
$rst = null;
$settings = [];


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

			$sql = "SELECT Key, [Value] FROM Setting WHERE Instance = $instance";

			$rst = $db->Execute($sql);

			while(!$rst->EOF){
				$settings[$rst['Key']->Value] = $rst['Value']->Value;
				
				$rst->MoveNext();
			}
			
			$rst->Close();
			$db->Close();
			
		}
		
	}
	
}
	
?>
<html>
<head>
	
	<meta charset="UTF-8">
	
	<title>Epic Stream Man's YouTube Player</title>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	
</head>
<body>
	
	<h1>YouTube Player Settings</h1>
	
	<form action="/set.php" method="post">
		
		<p>
		<label for="listType">List Type </label><select id="listType">
			<option value="playlist" <?php echo empty($settings['listType']) || $settings['listType'] == 'playlist' ? 'selected' : ''; ?>>playlist</option>
			<option value="user_uploads" <?php echo $settings['listType'] == 'user_uploads' ? 'selected' : ''; ?>>user uploads</option>
			<option value="search" <?php echo $settings['listType'] == 'search' ? 'selected' : ''; ?>>search results</option>
			<option value="playlist" <?php echo $settings['listType'] == 'playlist' ? 'selected' : ''; ?>>list of videos</option>
		</select>
		</p>
		
		<p>
		
		
	</form>
	
</body>
</html>
