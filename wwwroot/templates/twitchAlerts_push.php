<?php
error_reporting(E_ALL);
//ini_set('display_errors', 0);

header("Cache-Control: no-store, no-cache, max-age=0");
header("Expires: -1");

$template = 'twitchAlerts.php';

require '_getSettings.php';

if(!$errMsg && isset($_GET['type']) && isset($_GET['variation'])){	//request to queue an alert
	
	if($_GET['type'] != 'follower' && $_GET['type'] != 'subscriber' && $_GET['type'] != 'donator' && $_GET['type'] != 'host'){
		$errMsg = 'Invalid alert type';
	}
	else if(!is_numeric($_GET['variation']) || intval($_GET['variation']) < 0){
		$errMsg = 'Invalid variation number';
	}
	else{
		
		array_push($settingsArr['queue'], array("type"=>$_GET['type'], "variation"=>intval($_GET['variation'])));

		$settingsJson = json_encode($settingsArr);

		$modified = date('n/j/Y g:i:s A');

		$db = new COM('ADODB.Connection');
		$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");

		$cmd = new COM('ADODB.Command');
		$cmd->ActiveConnection = $db;
		$cmd->CommandText = 'UPDATE Instance SET Settings = ?, Modified = ? WHERE ID = ?';
		$cmd->CommandType = 1;	//adCmdText
		$cmd->Execute($_, array($settingsJson, $modified, $instance));

		$db->Close();
		
	}
	
}

if($errMsg){
	//problem with the data provided; display the error message
	http_response_code(200);	//Successful, OK
	echo 'On-screen alert error: '.$errMsg;
}
else{
	//success; changes have been made
	http_response_code(204);	//Successful, No Content
}
?>