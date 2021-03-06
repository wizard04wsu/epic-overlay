<?php

session_start();

if(!$_SESSION['user_id']){
	header('Location: https://epicstreamman.com/secure/epic-overlay/signIn.php');
	exit();
}

//declare variables (just for my sanity)
$errMsg = '';
$instance;
/*$dbPath;*/ $db; $cmd; $sql; $rst;
$pathParts;
$_;	//placeholder variable (need a variable to pass to $cmd->Execute(), but I don't care what gets put into it)
$title = ''; $settingsJson; $settingsArr; $modified;


if(empty($_GET['instance']) || !intval($_GET['instance'])){	//invalid instance ID
	$errMsg = 'Instance number is not specified.';
}
else{
	
	$instance = intval($_GET['instance']);
	
	//connect to the database
	require '../inc/dbPath.php';
	if(!file_exists($dbPath)){
		$errMsg = 'Could not find the database file.';
	}
	else{
		
		if(empty($template)){
			$pathParts = explode('/', $_SERVER['URL']);
			$template = $pathParts[count($pathParts)-1];
		}
		
		$db = new COM('ADODB.Connection');
		$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
		
		//make sure the instance number corresponds to an instance of this template
		$cmd = new COM('ADODB.Command');
		$cmd->ActiveConnection = $db;
		$cmd->CommandText = 'SELECT Instance.* FROM Instance INNER JOIN Template ON Instance.Template = Template.ID ' .
							'WHERE Template.Path = ? AND Instance.ID = ?';
		$cmd->CommandType = 1;	//adCmdText
		$rst = $cmd->Execute($_, array($template, $instance));
		
		
		if($rst->EOF){
			$errMsg = 'Specified instance does not use this template.';
		}
		else{
			$title = ''.$rst['Title'];
			$modified = ''.$rst['Modified'];
			$settingsJson = ''.$rst['Settings'];
			$settingsArr = json_decode($settingsJson, true);
		}
		
		//close the database connection
		$rst->Close();
		$db->Close();
		
	}
	
}
?>