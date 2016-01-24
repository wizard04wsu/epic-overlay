<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

header('Cache-Control: no-store, no-cache, max-age=0');
header("Expires: -1");

$template = 'twitchAlerts.php';

require '_getSettings.php';

if(!$errMsg){
	
	if(count($settingsArr['queue']) > 0){
		
		$settingsArr['queue'] = array();

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
	echo $errMsg;
}
else{
	//success; return the shifted alert
	http_response_code(200);	//Successful, OK
	echo 'success';
}
?>