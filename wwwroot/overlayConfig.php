<?php
error_reporting(E_ALL);
//ini_set('display_errors', 0);

header("Cache-Control: no-store, no-cache, max-age=0");
header("Expires: -1");
?><!DOCTYPE html>
<?php
//declare variables (just for my sanity)
$errMsg = '';
/*$dbPath;*/ $db; $sql; $rst;
$settingsArr; $key; $value;


//connect to the database
require 'dbPath.php';
if(!file_exists($dbPath)){
	$errMsg = 'Could not find the database file.';
}
else{
	$db = new COM('ADODB.Connection');
	$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
}
	
?>
<html>
<head>
	
	<meta charset="UTF-8">
	
	<title>Epic Stream Man's OBS Overlays Configuration</title>
	
	<style type="text/css" media="all">
		#container {
			display:inline-block;
			min-width:500px;
		}
		
		fieldset {
			margin-bottom: 1.5em;
			padding:16px 24px 24px;
			border:2px solid #6CF;
			box-shadow: inset 0 0 100px -50px #6CF, inset 0 0 12px -3px #6CF;
		}
		fieldset p:first-of-type {
			margin-top:0;
		}
		fieldset p:last-of-type {
			margin-bottom:0;
		}
		
		optgroup, option {
			padding:0 0 2px !important;
		}
		optgroup {
			margin-bottom:3px;
		}
		
		select, .settingsBox {
			border:2px inset #888;
			padding:6px 8px;
		}
		
		div.fillWidth {
			display:table;
			width:100%;
		}
		div.fillWidth > div {
			display:table-row;
		}
		div.fillWidth > div > div {
			display:table-cell;
			padding:0.25em 0;
		}
		div.fillWidth label {
			white-space:nowrap;
			padding-right:0.5em;
		}
		div.fillWidth > div > div:last-child {
			width:100%;
		}
		
		div.fillWidth > div > div:last-child input {
			box-sizing:border-box;
			width:100%;
		}
		
		select {
			width:100%;
		}
		select, optgroup, option {
			font-family:"Courier New",Courier,monospace;
			font-style:normal;
		}
		optgroup, option {
			padding-left:3px;
		}
		optgroup {
			font-weight:bold;
			text-decoration:underline;
		}
		
		iframe {
			width:100%;
			border:0;
		}
	</style>
	
	<script type="text/javascript" src="script/multiColumnSelect.js"></script>
	
</head>
<body>
<?php
	if($errMsg){
		echo $errMsg;
	}
	else{
?>
	<h1>OBS Overlays Configuration</h1>
	
	<div id="container">
	
	<fieldset>
		<legend>Templates</legend>
		<p>
		<select id="templates" size="5">
<?php
		$sql = "SELECT ID, Title, Path, Config FROM Template ORDER BY Title";
		$rst = $db->Execute($sql);
		while(!$rst->EOF){
			echo '<option id="'.$rst['ID'].'" '.($rst->AbsolutePosition == 1 ? 'selected' : '').'>' . $rst['Title'] . '</option>\n';
			$rst->MoveNext();
		}
		$rst->MoveFirst();
?>
		</select>
		</p>
		<div class="settingsBox">
			<div class="fillWidth">
				<div>
					<div><label for="templateTitle">Title</label></div>
					<div><input type="text" id="templateTitle" value="<?php echo $rst['Title']; ?>"></div>
				</div>
				<div>
					<div><label for="templatePath">Filename of template</label></div>
					<div><input type="text" id="templatePath" value="<?php echo $rst['Path']; ?>"></div>
				</div>
				<div>
					<div><label for="templateConfig">Filename of configuration</label></div>
					<div><input type="text" id="templateConfig" value="<?php echo $rst['Config']; ?>"></div>
				</div>
			</div>
			<p style="margin-top:0.75em;">
			<input type="button" id="templateSave" value="Save">
			</p>
		</div>
		<p>
		<input type="button" id="templateRegister" value="Register a new template">
		</p>
	</fieldset>
	
	<fieldset>
		<legend>Instances</legend>
		<p>
		<select id="instances" class="multiColumnSelect" size="8">
			<optgroup label="Title;Template"></optgroup>
<?php
		$sql = "SELECT Instance.ID, Instance.Title, Template.Title AS Template, Template.Path, Template.Config FROM Instance INNER JOIN Template ON Instance.Template = Template.ID ORDER BY Instance.Title, Template.Title";
		$rst = $db->Execute($sql);
		while(!$rst->EOF){
			echo '<option value="'.$rst['ID'].'" '.($rst->AbsolutePosition == 1 ? 'selected' : '').'>' . $rst['Title'].';'.$rst['Template'] . '</option>\n';
			$rst->MoveNext();
		}
		$rst->MoveFirst();
?>
		</select>
		</p>
		<p>
		<a id="instanceLink" href="templates/<?php echo $rst['Path'].'?instance='.$rst['ID']; ?>" target="_blank">Open instance in new window</a>
		</p>
		<div class="settingsBox">
			<iframe id="settings" src="templates/<?php echo $rst['Config'].'?instance='.$rst['ID']; ?>"></iframe>
		</div>
		<p>
		<input type="button" id="instanceCreate" value="Create a new instance">
		</p>
	</fieldset>
	
	</div>
	
	<script type="text/javascript">
		var sel_templates = document.getElementById("templates"),
			templateTitle = document.getElementById("templateTitle"),
			templatePath = document.getElementById("templatePath"),
			templateConfig = document.getElementById("templateConfig"),
			templateSave = document.getElementById("templateSave"),
			templateRegister = document.getElementById("templateRegister"),
			sel_instances = document.getElementById("instances"),
			instanceLink = document.getElementById("instanceLink"),
			settings = document.getElementById("settings");
		
		multiColumnSelect(";", "\u00a0\u00a0\u00a0\u00a0");
		
		sel_templates.addEventListener("change", templateChange, false);
		
		sel_instances.addEventListener("change", instanceChange, false);
		
		function templateChange(){
			var templateID = sel_templates.value;
			
			
		}
		
		function instanceChange(){
			//
		}
	</script>
<?php
		$rst->Close();
		$db->Close();
	}
?>
</body>
</html>
