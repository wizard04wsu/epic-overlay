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
$templateArr; $templateJson;
$instanceArr; $instanceJson;


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
			display:table;
		}
		
		fieldset {
			margin-bottom: 1.5em;
			padding:16px 24px 24px;
			float:left;
			margin-right:8px;
			min-width:500px;
		}
		fieldset p:first-of-type {
			margin-top:0;
		}
		fieldset p:last-of-type {
			margin-bottom:0;
		}
		#templatesSection {
			border:2px solid #6CF;
			box-shadow: inset 0 0 100px -50px #6CF, inset 0 0 12px -3px #6CF;
		}
		#instancesSection {
			border:2px solid #E8AB51;
			box-shadow: inset 0 0 100px -50px #E8AB51, inset 0 0 12px -3px #E8AB51;
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
			display:block;
			height:18.75em;
		}
	</style>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
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
	
	<fieldset id="templatesSection">
		<legend>Templates</legend>
		<p>
		<select id="templates" size="5">
<?php
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
			</p>
		</div>
		<p>
		<input type="button" id="templateRegister" value="Register a new template">
		<input type="button" id="templateRegister" value="Remove template">
		</p>
		<p>
		<input type="button" id="instanceCreate" value="Create an instance">
		</p>
		<script type="text/javascript">
			var i, templates = <?php echo $templateJson; ?>,
				sel_templates = $("#templates"),
				option;
			
			function textToHtml(str){
				return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#39;");
			}
			
			//add the template <option>s to the list
			for(i=0; i<templates.length; i++){
				option = document.createElement("option");
				option.value = templates[i].id;
				option.selected = i==0;
				option.innerHTML = textToHtml(templates[i].title);
				option.template = templates[i];
				sel_templates.append(option);
			}
			
			//fill in the template settings
			if(templates.length){
				$("#templateTitle")[0].value = templates[0].title;
				$("#templatePath")[0].value = templates[0].path;
				$("#templateConfig")[0].value = templates[0].config;
			}
			
			sel_templates.on("change", templateChange);
			
			function templateChange(evt){
				//TODO
			}
		</script>
	</fieldset>
	
	<fieldset id="instancesSection">
		<legend>Instances</legend>
		<p>
		<select id="instances" class="multiColumnSelect" size="8">
			<optgroup label="Title\Template"></optgroup>
<?php
		$instanceArr = array();
		$sql = "SELECT Instance.ID, Instance.Title, Template.ID AS TemplateID, Template.Title AS TemplateTitle, Template.Path, Template.Config FROM Instance INNER JOIN Template ON Instance.Template = Template.ID ORDER BY Instance.Title, Template.Title";
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
		</p>
		<div class="settingsBox" style="padding:0; margin-top:1.25em;">
			<iframe id="settings" src=""></iframe>
		</div>
		<script type="text/javascript">
			var instances = <?php echo $instanceJson; ?>,
				sel_instances = $("#instances");
			
			//add the instance <option>s to the list
			for(i=0; i<instances.length; i++){
				option = document.createElement("option");
				option.value = instances[i].id;
				option.selected = i==0;
				option.innerHTML = textToHtml(instances[i].title).replace(/\\/g, "&#92;")+"\\"+textToHtml(instances[i].template.title).replace(/\\/g, "&#92;");
				option.instance = instances[i];
				sel_instances.append(option);
			}
			
			multiColumnSelect("\\", "\u00a0\u00a0\u00a0\u00a0");
			
			$("#instanceLink")[0].href = "templates/"+instances[0].template.path+"?instance="+instances[0].id;
			$("#settings")[0].src = "templates/"+instances[0].template.config+"?instance="+instances[0].id;
			
			sel_instances.on("change", instanceChange);
			
			function instanceChange(evt){
				//TODO
			}
		</script>
	</fieldset>
	
	</div>
<?php
		$rst->Close();
		$db->Close();
	}
?>
</body>
</html>
