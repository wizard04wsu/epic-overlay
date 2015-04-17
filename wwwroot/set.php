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
$id;

if(empty($_POST['action'])){
	$errMsg = 'Action not specified.';
}
else if($_POST['action'] == 'saveInstance'){
	
	if(empty($_POST['instance']) || !intval($_POST['instance'])){
		$errMsg = 'Instance ID is not specified.';
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
			
			require 'dbPath.php';
			if(!file_exists($dbPath)){
				$errMsg = 'Could not find the database file.';
			}
			else{
				
				$db = new COM('ADODB.Connection');
				$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
				
				$cmd = new COM('ADODB.Command');
				$cmd->ActiveConnection = $db;
				$cmd->CommandText = 'UPDATE Instance SET Settings = ? WHERE ID = ?';
				$cmd->CommandType = 1;	//adCmdText
				$cmd->Execute($_, array($settingsJson, $instance));
				
				$db->Close();
				
			}
			
		}
		
	}
	
}
else if($_POST['action'] == 'deleteInstance'){
	
	if(empty($_POST['instance']) || !intval($_POST['instance'])){
		$errMsg = 'Instance ID is not specified.';
	}
	else{
		
		$instance = intval($_POST['instance']);
		
		require 'dbPath.php';
		if(!file_exists($dbPath)){
			$errMsg = 'Could not find the database file.';
		}
		else{
			
			$db = new COM('ADODB.Connection');
			$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
			
			$cmd = new COM('ADODB.Command');
			$cmd->ActiveConnection = $db;
			$cmd->CommandText = 'DELETE * FROM Instance WHERE ID = ?';
			$cmd->CommandType = 1;	//adCmdText
			$cmd->Execute($_, array($instance));
			
			$db->Close();
			
		}
		
	}
	
}
else if($_POST['action'] == 'createInstance'){
	
	if(empty($_POST['template']) || !intval($_POST['template'])){
		$errMsg = 'Template ID is not specified.';
	}
	else if(empty($_POST['title'])){
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
			
			require 'dbPath.php';
			if(!file_exists($dbPath)){
				$errMsg = 'Could not find the database file.';
			}
			else{
				
				$db = new COM('ADODB.Connection');
				$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
				
				$cmd = new COM('ADODB.Command');
				$cmd->ActiveConnection = $db;
				$cmd->CommandText = 'INSERT INTO Instance (Title, Template, Settings) VALUES (?, ?, ?)';
				$cmd->CommandType = 1;	//adCmdText
				$cmd->Execute($_, array($_POST['title'], $_POST['template'], $settingsJson));
				
				//get the newly created instance ID
				$rst = new COM('ADODB.Recordset');
				$rst->Open('SELECT @@IDENTITY', $db);
				$id = intval($rst[0]);
				
				$db->Close();
				
				//respond with the instance ID
				exit(json_encode($id));
				
			}
			
		}
		
	}
	
}
else if($_POST['action'] == 'saveTemplate'){
	
	if(empty($_POST['template']) || !intval($_POST['template'])){
		$errMsg = 'Template ID is not specified.';
	}
	else{
		
		$instance = intval($_POST['template']);
		
		require 'dbPath.php';
		if(!file_exists($dbPath)){
			$errMsg = 'Could not find the database file.';
		}
		else{
			
			$db = new COM('ADODB.Connection');
			$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
			
			$cmd = new COM('ADODB.Command');
			$cmd->ActiveConnection = $db;
			$cmd->CommandText = 'UPDATE Template SET Title = ?, Path = ?, Config = ? WHERE ID = ?';
			$cmd->CommandType = 1;	//adCmdText
			$cmd->Execute($_, array($_POST['title'], $_POST['path'], $_POST['config'], $template));
			
			$db->Close();
			
		}
		
	}
	
}
else if($_POST['action'] == 'registerTemplate'){
	
	if(empty($_POST['template']) || !intval($_POST['template'])){
		$errMsg = 'Template ID is not specified.';
	}
	else if(empty($_POST['title'])){
		$errMsg = 'Title is not specified.';
	}
	else if(empty($_POST['path'])){
		$errMsg = 'Path is not specified.';
	}
	else if(empty($_POST['config'])){
		$errMsg = 'Config is not specified.';
	}
	else{
		
		require 'dbPath.php';
		if(!file_exists($dbPath)){
			$errMsg = 'Could not find the database file.';
		}
		else{
			
			$db = new COM('ADODB.Connection');
			$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
			
			$cmd = new COM('ADODB.Command');
			$cmd->ActiveConnection = $db;
			$cmd->CommandText = 'INSERT INTO Template (Title, Path, Config) VALUES (?, ?, ?)';
			$cmd->CommandType = 1;	//adCmdText
			$cmd->Execute($_, array($_POST['title'], $_POST['path'], $_POST['config'], $template));
			
			//get the newly created instance ID
			$rst = new COM('ADODB.Recordset');
			$rst->Open('SELECT @@IDENTITY', $db);
			$id = intval($rst[0]);
			
			$db->Close();
			
			//respond with the template ID
			exit(json_encode($id));
			
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