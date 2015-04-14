<?php
error_reporting(E_ALL);
//ini_set('display_errors', 0);

header("Cache-Control: no-store, no-cache, max-age=0");
header("Expires: -1");
?><!DOCTYPE html>
<?php
//declare variables (just for my sanity)
$errMsg = '';
$dbPath; $db; $sql; $rst;
$settingsArr; $key; $value;


//connect to the database
$dbPath = realpath($_SERVER['DOCUMENT_ROOT'].'/../data/overlayConfig.accdb');
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
			padding:1em 1.5em 1.5em;
		}
		fieldset p:first-of-type {
			margin-top:0;
		}
		fieldset p:last-of-type {
			margin-bottom:0;
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
		<select id="templates" class="multiColumnSelect" size="10">
			<optgroup label="Title;Path"></optgroup>
<?php
		$sql = "SELECT ID, Title, Path, Config FROM Template ORDER BY Title";
		$rst = $db->Execute($sql);
		while(!$rst->EOF){
			echo '<option id="'.$rst['ID'].'" '.($rst->AbsolutePosition == 1 ? 'selected' : '').'>' . $rst['Title'].';'.$rst['Path'] . '</option>\n';
			$rst->MoveNext();
		}
		$rst->MoveFirst();
?>
		</select>
		</p>
		<fieldset>
			<div class="fillWidth">
				<div>
					<div><label for="templateTitle">Title</label></div>
					<div><input type="text" id="templateTitle" value="<?php echo $rst['Title']; ?>" style="width:100%;"></div>
				</div>
				<div>
					<div><label for="templatePath">Path to template</label></div>
					<div><input type="text" id="templatePath" value="<?php echo $rst['Path']; ?>" style="width:100%;"></div>
				</div>
				<div>
					<div><label for="templateConfig">Path to configuration</label></div>
					<div><input type="text" id="templateConfig" value="<?php echo $rst['Config']; ?>" style="width:100%;"></div>
				</div>
			</div>
			<p style="margin-top:0.75em;">
			<input type="button" id="templateSave" value="Save">
			</p>
		</fieldset>
		<p>
		<input type="button" id="templateRegister" value="Register a new template">
		</p>
	</fieldset>
	
	<fieldset>
		<legend>Instances</legend>
		<p>
		<select id="instances" class="multiColumnSelect" size="10">
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
		<a id="instanceLink" href="<?php echo $rst['Path'].'?instance='.$rst['ID']; ?>" target="_blank">Open instance in new window</a>
		</p>
		<fieldset>
			<iframe id="settings" src="<?php echo $rst['Config'].'?instance='.$rst['ID']; ?>"></iframe>
		</fieldset>
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
