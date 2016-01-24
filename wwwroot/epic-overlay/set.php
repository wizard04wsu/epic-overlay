<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header('Cache-Control: no-store, no-cache, max-age=0');
header("Expires: -1");


//declare variables (just for my sanity)
$errMsg = '';
$settingsArr; $settingsJson = '';
$instance;
/*$dbPath;*/ $db; $cmd; $sql; $rst;
$id; $title; $modified;
$admin = false;

function encodeSettingsValues(&$arr){
	foreach($arr as &$v){
		if(is_array($v)){
			encodeSettingsValues($v);
		}
		else{
			$v = htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
		}
	}
}

function respondWithError($errMsg){
	http_response_code(200);	//Successful, OK
	echo $errMsg;
	exit();
}

//connect to the databse
require 'inc/dbPath.php';
if(!file_exists($dbPath)){
	respondWithError('Could not find the database file.');
}
$db = new COM('ADODB.Connection');
$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");

//is the user an admin?
$sql = 'SELECT * FROM [User] WHERE ID = '.$_SESSION['user_id'].' AND Role.Value = "Administrator"';
$rst = $db->Execute($sql);
if(!$rst->EOF){
	$admin = true;
}

if(empty($_POST['action'])){
	respondWithError('Action not specified.');
}
else if($_POST['action'] == 'saveInstance'){
	
	if(empty($_POST['instance']) || !intval($_POST['instance'])){	//invalid instance ID
		respondWithError('Instance ID is not specified.');
	}
	if(empty($_POST['title'])){	//instance title not provided
		respondWithError('Title is not specified.');
	}
	
	//make sure settings is a valid JSON string
	@$settingsJson = $_POST['settings'];
	$settingsArr = json_decode($settingsJson, true);
	if($settingsJson != json_encode($settingsArr, JSON_UNESCAPED_SLASHES)){
		respondWithError('Settings are malformed.');
	}
	
	$instance = intval($_POST['instance']);
	$title = htmlspecialchars(''.$_POST['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
	
	//html encode the settings values
	encodeSettingsValues($settingsArr);
	$settingsJson = json_encode($settingsArr);
	
	//create a new timestamp
	$modified = date('n/j/Y g:i:s A');
	
	//update the record
	$cmd = new COM('ADODB.Command');
	$cmd->ActiveConnection = $db;
	$cmd->CommandText = 'UPDATE Instance SET Title = ?, Settings = ?, Modified = ? WHERE ID = ? AND UserID = ?';
	$cmd->CommandType = 1;	//adCmdText
	$cmd->Execute($_, array($title, $settingsJson, $modified, $instance, $_SESSION['user_id']));
	
}
else if($_POST['action'] == 'deleteInstance'){
	
	if(empty($_POST['instance']) || !intval($_POST['instance'])){	//invalid instance ID
		respondWithError('Instance ID is not specified.');
	}
	
	$instance = intval($_POST['instance']);
	
	//delete the record
	$cmd = new COM('ADODB.Command');
	$cmd->ActiveConnection = $db;
	$cmd->CommandText = 'DELETE * FROM Instance WHERE ID = ? AND UserID = ?';
	$cmd->CommandType = 1;	//adCmdText
	$cmd->Execute($_, array($instance, $_SESSION['user_id']));
	
}
else if($_POST['action'] == 'createInstance'){
	
	if(empty($_POST['template']) || !intval($_POST['template'])){	//invalid template ID
		respondWithError('Template ID is not specified.');
	}
	if(empty($_POST['title'])){	//instance title not provided
		respondWithError('Title is not specified.');
	}
	
	//make sure settings is a valid JSON string
	@$settingsJson = $_POST['settings'];
	$settingsArr = json_decode($settingsJson, true);
	if($settingsJson != json_encode($settingsArr, JSON_UNESCAPED_SLASHES)){
		respondWithError('Settings are malformed.');
	}
	
	//create a new timestamp
	$modified = date('n/j/Y g:i:s A');
	
	//add the record
	$cmd = new COM('ADODB.Command');
	$cmd->ActiveConnection = $db;
	$cmd->CommandText = 'INSERT INTO Instance (UserID, Title, Template, Settings, Modified) VALUES (?, ?, ?, ?, ?)';
	$cmd->CommandType = 1;	//adCmdText
	$cmd->Execute($_, array($_SESSION['user_id'], $_POST['title'], $_POST['template'], $settingsJson, $modified));
	
	//get the newly created instance ID
	$rst = new COM('ADODB.Recordset');
	$rst->Open('SELECT @@IDENTITY', $db);
	$id = intval($rst[0]);
	
	
	//respond with the instance ID
	exit(json_encode($id));
	
}
else if($_POST['action'] == 'saveTemplate'){
	
	if(!$admin){	//user doesn't have permission to perform this action
		respondWithError('Action not allowed.');
	}
	if(empty($_POST['template']) || !intval($_POST['template'])){	//invalid template ID
		respondWithError('Template ID is not specified.');
	}
	
	$template = intval($_POST['template']);
	
	//update the record
	$cmd = new COM('ADODB.Command');
	$cmd->ActiveConnection = $db;
	$cmd->CommandText = 'UPDATE Template SET Title = ?, Path = ?, Config = ? WHERE ID = ?';
	$cmd->CommandType = 1;	//adCmdText
	$cmd->Execute($_, array($_POST['title'], $_POST['path'], $_POST['config'], $template));
	
}
else if($_POST['action'] == 'registerTemplate'){
	
	if(!$admin){	//user doesn't have permission to perform this action
		respondWithError('Action not allowed.');
	}
	if(empty($_POST['title'])){	//template title not specified
		respondWithError('Title is not specified.');
	}
	if(empty($_POST['path'])){	//template filename not specified
		respondWithError('Path is not specified.');
	}
	if(empty($_POST['config'])){	//template settings filename not specified
		respondWithError('Config is not specified.');
	}
	
	//add the record
	$cmd = new COM('ADODB.Command');
	$cmd->ActiveConnection = $db;
	$cmd->CommandText = 'INSERT INTO Template (Title, Path, Config) VALUES (?, ?, ?)';
	$cmd->CommandType = 1;	//adCmdText
	$cmd->Execute($_, array($_POST['title'], $_POST['path'], $_POST['config']));
	
	//get the newly created template ID
	$rst = new COM('ADODB.Recordset');
	$rst->Open('SELECT @@IDENTITY', $db);
	$id = intval($rst[0]);
	
	
	//respond with the template ID
	exit(json_encode($id));
	
}
else if($_POST['action'] == 'removeTemplate'){
	
	if(!$admin){	//user doesn't have permission to perform this action
		respondWithError('Action not allowed.');
	}
	if(empty($_POST['template']) || !intval($_POST['template'])){	//invalid template ID
		respondWithError('Template ID is not specified.');
	}
	
	$template = intval($_POST['template']);
	
	//delete the record
	$cmd = new COM('ADODB.Command');
	$cmd->ActiveConnection = $db;
	$cmd->CommandText = 'DELETE * FROM Template WHERE ID = ?';
	$cmd->CommandType = 1;	//adCmdText
	$cmd->Execute($_, array($template));
	
}
else{
	respondWithError('Unknown action.');
}

//close the database connection
$rst->Close();
$db->Close();

//success; reload the dashboard
http_response_code(205);	//Successful, Reset Content
?>