<?php
error_reporting(E_ALL);
//ini_set('display_errors', 0);

header("Cache-Control: no-store, no-cache, max-age=0");
header("Expires: -1");


//declare variables (just for my sanity)
$errMsg = '';
$settingsArr; $settingsJson = '';
$instance;
/*$dbPath;*/ $db; $cmd; $sql; $rst;
$id; $title; $modified;

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

if(empty($_POST['action'])){
	$errMsg = 'Action not specified.';
}
else if($_POST['action'] == 'saveInstance'){
	
	if(empty($_POST['instance']) || !intval($_POST['instance'])){	//invalid instance ID
		$errMsg = 'Instance ID is not specified.';
	}
	else if(empty($_POST['title'])){	//instance title not provided
		$errMsg = 'Title is not specified.';
	}
	else{
		
		//make sure settings is a valid JSON string
		@$settingsJson = $_POST['settings'];
		$settingsArr = json_decode($settingsJson, true);
		if($settingsJson != json_encode($settingsArr)){
			$errMsg = 'Settings are malformed.';
		}
		else{
			
			//connect to the databse
			require 'inc/dbPath.php';
			if(!file_exists($dbPath)){
				$errMsg = 'Could not find the database file.';
			}
			else{
				$db = new COM('ADODB.Connection');
				$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
				
				
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
				$cmd->CommandText = 'UPDATE Instance SET Title = ?, Settings = ?, Modified = ? WHERE ID = ?';
				$cmd->CommandType = 1;	//adCmdText
				$cmd->Execute($_, array($title, $settingsJson, $modified, $instance));
				
				
				//close the database connection
				$db->Close();
			}
			
		}
		
	}
	
}
else if($_POST['action'] == 'deleteInstance'){
	
	if(empty($_POST['instance']) || !intval($_POST['instance'])){	//invalid instance ID
		$errMsg = 'Instance ID is not specified.';
	}
	else{
		
		$instance = intval($_POST['instance']);
		
		require 'inc/dbPath.php';
		if(!file_exists($dbPath)){
			$errMsg = 'Could not find the database file.';
		}
		else{
			//connect to the database
			$db = new COM('ADODB.Connection');
			$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
			
			
			//delete the record
			$cmd = new COM('ADODB.Command');
			$cmd->ActiveConnection = $db;
			$cmd->CommandText = 'DELETE * FROM Instance WHERE ID = ?';
			$cmd->CommandType = 1;	//adCmdText
			$cmd->Execute($_, array($instance));
			
			
			//close the database connection
			$db->Close();
		}
		
	}
	
}
else if($_POST['action'] == 'createInstance'){
	
	if(empty($_POST['template']) || !intval($_POST['template'])){	//invalid template ID
		$errMsg = 'Template ID is not specified.';
	}
	else if(empty($_POST['title'])){	//instance title not provided
		$errMsg = 'Title is not specified.';
	}
	else{
		
		//make sure settings is a valid JSON string
		@$settingsJson = $_POST['settings'];
		$settingsArr = json_decode($settingsJson, true);
		if($settingsJson != json_encode($settingsArr)){
			$errMsg = 'Settings are malformed.';
		}
		else{
			
			//connect to the database
			require 'inc/dbPath.php';
			if(!file_exists($dbPath)){
				$errMsg = 'Could not find the database file.';
			}
			else{
				$db = new COM('ADODB.Connection');
				$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
				
				
				//create a new timestamp
				$modified = date('n/j/Y g:i:s A');
				
				//add the record
				$cmd = new COM('ADODB.Command');
				$cmd->ActiveConnection = $db;
				$cmd->CommandText = 'INSERT INTO Instance (Title, Template, Settings, Modified) VALUES (?, ?, ?, ?)';
				$cmd->CommandType = 1;	//adCmdText
				$cmd->Execute($_, array($_POST['title'], $_POST['template'], $settingsJson, $modified));
				
				//get the newly created instance ID
				$rst = new COM('ADODB.Recordset');
				$rst->Open('SELECT @@IDENTITY', $db);
				$id = intval($rst[0]);
				
				
				//close the database connection
				$rst->Close();
				$db->Close();
				
				
				//respond with the instance ID
				exit(json_encode($id));
				
			}
			
		}
		
	}
	
}
else if($_POST['action'] == 'saveTemplate'){
	
	if(empty($_POST['template']) || !intval($_POST['template'])){	//invalid template ID
		$errMsg = 'Template ID is not specified.';
	}
	else{
		
		$template = intval($_POST['template']);
		
		//connect to the database
		require 'inc/dbPath.php';
		if(!file_exists($dbPath)){
			$errMsg = 'Could not find the database file.';
		}
		else{
			$db = new COM('ADODB.Connection');
			$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
			
			
			//update the record
			$cmd = new COM('ADODB.Command');
			$cmd->ActiveConnection = $db;
			$cmd->CommandText = 'UPDATE Template SET Title = ?, Path = ?, Config = ? WHERE ID = ?';
			$cmd->CommandType = 1;	//adCmdText
			$cmd->Execute($_, array($_POST['title'], $_POST['path'], $_POST['config'], $template));
			
			
			//close the database connection
			$db->Close();
		}
		
	}
	
}
else if($_POST['action'] == 'registerTemplate'){
	
	if(empty($_POST['title'])){	//template title not specified
		$errMsg = 'Title is not specified.';
	}
	else if(empty($_POST['path'])){	//template filename not specified
		$errMsg = 'Path is not specified.';
	}
	else if(empty($_POST['config'])){	//template settings filename not specified
		$errMsg = 'Config is not specified.';
	}
	else{
		
		//connect to the database
		require 'inc/dbPath.php';
		if(!file_exists($dbPath)){
			$errMsg = 'Could not find the database file.';
		}
		else{
			$db = new COM('ADODB.Connection');
			$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
			
			
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
			
			
			//close the database connection
			$rst->Close();
			$db->Close();
			
			
			//respond with the template ID
			exit(json_encode($id));
			
		}
		
	}
	
}
else if($_POST['action'] == 'removeTemplate'){
	
	if(empty($_POST['template']) || !intval($_POST['template'])){	//invalid template ID
		$errMsg = 'Template ID is not specified.';
	}
	else{
		
		$template = intval($_POST['template']);
		
		//connect to the database
		require 'inc/dbPath.php';
		if(!file_exists($dbPath)){
			$errMsg = 'Could not find the database file.';
		}
		else{
			$db = new COM('ADODB.Connection');
			$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
			
			
			//delete the record
			$cmd = new COM('ADODB.Command');
			$cmd->ActiveConnection = $db;
			$cmd->CommandText = 'DELETE * FROM Template WHERE ID = ?';
			$cmd->CommandType = 1;	//adCmdText
			$cmd->Execute($_, array($template));
			
			
			//close the database connection
			$db->Close();
		}
		
	}
	
}
else{
	$errMsg = 'Unknown action.';
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