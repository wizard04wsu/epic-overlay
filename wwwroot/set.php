<?php
error_reporting(E_ALL);
//ini_set('display_errors', 0);

header("Cache-Control: no-store, no-cache, max-age=0");
header("Expires: -1");

//declare variables (just for my sanity)
$errMsg = '';
$settingsArr; $settingsJson = '';
$instance;
$dbPath; $db; $cmd; $sql; $rst;

if(empty($_POST['instance']) || !intval($_POST['instance'])){
	$errMsg = 'Instance number is not specified.';
}
else{
	
	//make sure settings is a valid JSON string
	@$settingsJson = $_POST['settings'];
	$settingsArr = json_decode($settingsJson, true);
	if($settingsJson != json_encode($settingsArr)){
		$errMsg = 'Settings are malformed.';
	}
	else{
		
		$instance = intval($_POST['instance']);
		
		$dbPath = realpath($_SERVER['DOCUMENT_ROOT'].'/../data/overlayConfig.accdb');
		if(!file_exists($dbPath)){
			$errMsg = 'Could not find the database file.';
		}
		else{
			
			$db = new COM('ADODB.Connection');
			$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
			
			$cmd = new COM('ADODB.Command');
			$cmd->ActiveConnection = $db;
			$cmd->CommandText = 'UPDATE Instance SET Settings = ? WHERE Instance.ID = ?';
			$cmd->CommandType = 1;	//adCmdText
			$cmd->Execute($_, array($settingsJson, $instance));
			
			$db->Close();
			
		}
		
	}
}

if($errMsg){
	//problem with the data provided; display the error message
	http_response_code(200);	//Successful, OK
	echo $errMsg;
}
else{
	//success; reload the configuration page
	http_response_code(205);	//Successful, Reset Content
}
?>