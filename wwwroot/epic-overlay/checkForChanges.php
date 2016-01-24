<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

header('Cache-Control: no-store, no-cache, max-age=0');
header("Expires: -1");


//declare variables (just for my sanity)
$errMsg = '';
$instance;
/*$dbPath;*/ $db; $cmd; $sql; $rst;
$timestamp = ''; $modified;

if(empty($_GET['instance']) || !intval($_GET['instance'])){	//invalid instance ID provided
	$errMsg = 'Instance number is not specified.';
}
else{
	
	$instance = intval($_GET['instance']);
	
	//get the timestamp if provided
	if(!empty($_GET['timestamp'])){
		$timestamp = $_GET['timestamp'];
	}
	
	//connect to the database
	require 'inc/dbPath.php';
	if(!file_exists($dbPath)){
		$errMsg = 'Could not find the database file.';
	}
	else{
		$db = new COM('ADODB.Connection');
		$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
		
		
		//get the modification timestamp from the database
		$cmd = new COM('ADODB.Command');
		$cmd->ActiveConnection = $db;
		$cmd->CommandText = 'SELECT Modified FROM Instance WHERE Instance.ID = ?';
		$cmd->CommandType = 1;	//adCmdText
		$rst = $cmd->Execute($_, array($instance));
		
		if($rst->EOF){
			$errMsg = 'Specified instance does not exist.';
		}
		else{
			$modified = ''.$rst['Modified'];
		}
		
		
		//close the database connection
		$rst->Close();
		$db->Close();
	}
	
}

if($errMsg){
	//problem with the data provided; display the error message
	http_response_code(200);	//Successful, OK
	echo $errMsg;
}
elseif($timestamp == $modified){
	//no change
	http_response_code(204);	//Successful, No Content
}
else{
	//changes have been made
	http_response_code(205);	//Successful, Reset Content
}
?>