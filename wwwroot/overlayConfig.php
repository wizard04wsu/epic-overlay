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
			padding:6px 8px 8px;
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
		<select id="templates" size="7">
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
		<p style="text-align:center;">
		
		<input type="button" id="instanceCreate" value="Create an instance">
		</p>
		<script type="text/javascript">
			var i, templates = <?php echo $templateJson; ?>,
				sel_templates = $("#templates"),
				option,
				currentTemplate;
			
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
			
			sel_templates.on("change", templateChange);
			templateChange();
			
			$("#templateRegister").on("click", registerTemplate);
			
			$("#instanceCreate").on("click", createInstance);
			
			function templateChange(){
				currentTemplate = $("#templates option:selected")[0];
				if(currentTemplate){
					currentTemplate = currentTemplate.template;
					//populate fields
					$("#templateTitle")[0].value = currentTemplate.title;
					$("#templatePath")[0].value = currentTemplate.path;
					$("#templateConfig")[0].value = currentTemplate.config;
					//enable buttons
					$("#templateRemove")[0].disabled = $("#instanceCreate")[0].disabled = false;
				}
				else{	//no template selected
					//clear fields
					$("#templateTitle")[0].value = $("#templatePath")[0].value = $("#templateConfig")[0].value = "";
					//disable buttons
					$("#templateRemove")[0].disabled = $("#instanceCreate")[0].disabled = true;
				}
			}
			
			function registerTemplate(){
				//deselect the current template
				$("#templates option:selected").each(function (){ this.selected = false; });
				currentTemplate = null;
				templateChange();
				//put cursor in Title field
				$("#templateTitle")[0].focus();
			}
			
			function createInstance(){
				
				var template = currentTemplate;
				
				//get default template settings
				$.ajax({
					url: 'templates/'+template.config,
					method: 'POST',
					data: {
						getDefaults: "1"
					}
				}).done(function(content, message, xhr) {
					
					var title;
					
					if (200 !== xhr.status) {	//error returned
						//display the error message
						alert("Failed to retrieve default settings:\n\n"+content);
						return;
					}
					
					//success
					
					//have the user input a title
					title = prompt("Please enter the title.");
					while(title.trim() === ""){
						title = prompt("The title cannot be empty.\n\nPlease enter the title.");
					}
					if(title !== null){
						
						title = title.trim();
						
						//add the instance to the database
						$.ajax({
							url: 'set.php',
							method: 'POST',
							data: {
								action: "createInstance",
								title: title,
								template: template.id,
								settings: content
							}
						}).done(function(content, message, xhr) {
							
							var option, id;
							
							if (200 !== xhr.status) {	//error returned
								//display the error message
								alert("Failed to create the instance:\n\n"+content);
								return;
							}
							
							//success; get the ID
							id = 1*JSON.parse(content);
							if(id <= 0 || id !== Math.floor(id)){
								alert("Invalid instance ID: "+id);
								return;
							}
							
							//add the instance <option> to the list
							option = document.createElement("option");
							option.value = id;
							option.innerHTML = textToHtml(title).replace(/\\/g, "&#92;")+"\\"+textToHtml(template.title).replace(/\\/g, "&#92;");
							option.instance = {id: id, title: title, template: template};
							sel_instances.append(option);
							multiColumnSelect("\\", "\u00a0\u00a0\u00a0\u00a0");
							option.selected = true;
							instanceChange();
							
						}).fail(function(xhr, message, errorThrown) {
							//display a generic error message
							alert("Failed to create the instance:\n\n"+message+"\n\n"+errorThrown);
						});
						
					}
					
					
				}).fail(function(xhr, message, errorThrown) {
					//display a generic error message
					alert("Failed to retrieve default settings:\n\n"+message+"\n\n"+errorThrown);
				});
				
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
			<input type="button" id="instanceDelete" value="Delete" style="float:right;">
			<div style="clear:both;"></div>
		</p>
		<div class="settingsBox" style="padding:0; margin-top:1.25em;">
			<iframe id="settings" src=""></iframe>
		</div>
		<script type="text/javascript">
			var instances = <?php echo $instanceJson; ?>,
				sel_instances = $("#instances"),
				iframe = $("#settings")[0],
				currentInstance;
			
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
			
			iframe.addEventListener("load", updateIframeHeight, false);
			
			sel_instances.on("change", instanceChange);
			instanceChange();
			
			$("#instanceDelete").on("click", deleteInstance);
			
			function instanceChange(evt){
				currentInstance = $("#instances option:selected")[0];
				if(currentInstance){
					currentInstance = currentInstance.instance;
					$("#instanceLink")[0].style.visibility = "visible";
					$("#instanceLink")[0].href = "templates/"+currentInstance.template.path+"?instance="+currentInstance.id;
					iframe.src = "templates/"+currentInstance.template.config+"?instance="+currentInstance.id;
				}
				else{
					$("#instanceLink")[0].style.visibility = "hidden";
					iframe.src = "";
				}
			}
			
			function updateIframeHeight(){
				iframe.style.height = iframe.contentWindow.document.body.clientHeight + "px";
			}
			
			function deleteInstance(){
				if(confirm("Are you sure you want to delete this instance of the "+currentInstance.template.title+" template?\n\n"+currentInstance.title)){
					$.ajax({
						url: 'set.php',
						method: 'POST',
						data: {
							instance: currentInstance.id,
							action: "deleteInstance"
						}
					}).done(function(content, message, xhr) {
						
						var remainingOptions;
						
						if (205 !== xhr.status) {	//error returned
							//display the error message
							alert("Failed to delete instance:\n\n"+content);
							return;
						}
						
						//success; remove the instance from the list
						$("#instances option:selected").remove();
						remainingOptions = $("#instances option");
						if(remainingOptions[0]) remainingOptions[0].selected = true;
						instanceChange();
						
					}).fail(function(xhr, message, errorThrown) {
						//display a generic error message
						alert("Failed to delete instance:\n\n"+message+"\n\n"+errorThrown);
					});
				}
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
