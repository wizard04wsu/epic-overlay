<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if(!$_SESSION['user_id']){
	header('Location: https://epicstreamman.com/secure/epic-overlay/signIn.php');
	exit();
}

header('Cache-Control: no-store, no-cache, max-age=0');
header('Expires: -1');

//declare variables (just for my sanity)
$errMsg = '';
/*$dbPath;*/ $db; $sql; $rst;
$templateArr; $templateJson;
$instanceArr; $instanceJson;
$admin = false;


//connect to the database
require 'inc/dbPath.php';
if(!file_exists($dbPath)){
	$errMsg = 'Could not find the database file.';
}
else{
	$db = new COM('ADODB.Connection');
	$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
	
	//is the user an admin?
	$sql = 'SELECT * FROM [User] WHERE ID = '.$_SESSION['user_id'].' AND Role.Value = "Administrator"';
	$rst = $db->Execute($sql);
	if(!$rst->EOF){
		$admin = true;
	}
}
	
?><!DOCTYPE html>

<html>
<head>
	
	<meta charset="UTF-8">
	
	<title>Epic Overlay Dashboard</title>
	
	<link rel="stylesheet" media="all" href="inc/dashboard.css">
	
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script type="text/javascript" src="inc/jquery.sortElements.js"></script>	<!-- https://github.com/padolsey-archive/jquery.fn/tree/master/sortElements -->
	<script type="text/javascript" src="inc/htmlEncode.js"></script>
	<script type="text/javascript" src="inc/multiColumnSelect.js"></script>
	<script type="text/javascript" src="inc/dashboard.js"></script>
	
    <meta name="google-signin-scope" content="profile email">
    <meta name="google-signin-client_id" content="977450567667-btt3bsju6boeg0hdcqjl9n8dv5s2s1s7.apps.googleusercontent.com">
    <script src="https://apis.google.com/js/platform.js" defer></script>
	
</head>
<body>
	
	<div class="g-signin2" style="display:none;"></div>
	<a href="#" onclick="signOut();">Sign out</a>
	<script>
		function signOut() {
			var auth2 = gapi.auth2.getAuthInstance();
			auth2.signOut().then(function () {
				console.log('User signed out.');
				window.location.replace("https://epicstreamman.com/secure/epic-overlay/gsi/signOut.php");
			});
		}
	</script>
	
<?php
	if($errMsg){
		echo $errMsg;
	}
	else{
?>
	<div style="text-align:center; margin-bottom:2em;">
		<h1 style="margin-bottom:0.1em;">Epic Overlay Dashboard</h1>
		<p style="font-size:80%; margin-top:0;">HTML overlays for use in the Open Broadcaster Software CLR browser</p>
	</div>
	
	<div id="container">
	
	<fieldset id="templatesSection">
		<legend>Templates</legend>
		<p>
		<select id="templates" size="7">
<?php
		//get the list of templates from the database
		$templateArr = array();
		$sql = "SELECT * FROM Template ORDER BY Title";
		$rst = $db->Execute($sql);
		while(!$rst->EOF){
			array_push($templateArr, array('id'=>$rst['ID']->Value, 'title'=>''.$rst['Title'], 'path'=>''.$rst['Path'], 'config'=>''.$rst['Config']));
			$rst->MoveNext();
		}
		$templateJson = json_encode($templateArr);
?>
		</select>
		</p>
<?php
if($admin){
?>
		<p>
			<input type="button" id="templateRegister" value="Register a new template">
			<input type="button" id="templateRemove" value="Remove" style="float:right;">
			<div style="clear:both;"></div>
		</p>
		<div class="settingsBox">
			<div class="fillWidth">
				<div>
					<div><label for="templateTitle">Title</label></div>
					<div><input type="text" id="templateTitle" value=""></div>
				</div>
				<div>
					<div><label for="templatePath">Filename of template</label></div>
					<div><input type="text" id="templatePath" value=""></div>
				</div>
				<div>
					<div><label for="templateConfig">Filename of configuration</label></div>
					<div><input type="text" id="templateConfig" value=""></div>
				</div>
			</div>
			<p style="margin-top:0.75em;">
			<input type="button" id="templateSave" value="Save">
			<input type="button" id="templateCancel" value="Cancel">
			</p>
		</div>
<?php
}
?>
		<p style="text-align:center;">
		
		<input type="button" id="instanceCreate" value="Create an instance">
		</p>
	</fieldset>
	
	<fieldset id="instancesSection">
		<legend>Instances</legend>
		<p>
		<select id="instances" class="multiColumnSelect" size="8">
			<optgroup label="Title\Template"></optgroup>
<?php
		//get the list of instances from the database
		$instanceArr = array();
		$sql = "SELECT Instance.ID, Instance.Title, Template.ID AS TemplateID, Template.Title AS TemplateTitle, Template.Path, Template.Config FROM Instance INNER JOIN Template ON Instance.Template = Template.ID WHERE Instance.UserID = {$_SESSION['user_id']} ORDER BY Instance.Title, Template.Title";
		$rst = $db->Execute($sql);
		while(!$rst->EOF){
			array_push($instanceArr, array('id'=>$rst['ID']->Value, 'title'=>''.$rst['Title'], 'template'=>array('id'=>$rst['TemplateID']->Value, 'title'=>''.$rst['TemplateTitle'], 'path'=>''.$rst['Path'], 'config'=>''.$rst['Config'])));
			$rst->MoveNext();
		}
		$instanceJson = json_encode($instanceArr);
?>
		</select>
		</p>
		<p>
			<a id="instanceLink" href="#" target="_blank">Open instance in new window</a>
			<input type="button" id="instanceDelete" value="Delete" style="float:right;">
			<div style="clear:both;"></div>
		</p>
		<div class="settingsBox" style="padding:0; margin-top:1.25em;">
			<iframe id="settings" src=""></iframe>
		</div>
		<script type="text/javascript">
			initTemplates(<?php echo $templateJson; ?>);
			initInstances(<?php echo $instanceJson; ?>);
		</script>
	</fieldset>
	
	</div>
<?php
		//close the database connection
		$rst->Close();
		$db->Close();
	}
?>
</body>
</html>
