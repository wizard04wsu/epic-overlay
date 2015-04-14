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
		fieldset {
			margin-bottom: 1.5em;
		}
		table {
			border-collapse: collapse;
			border: 1px solid #AAA;
		}
		th {
			border-bottom: 1px solid #AAA;
		}
		th, td {
			text-align: left;
			padding: 0.25em 0.5em;
		}
		
		optgroup, option {
			font-family:Consolas,Menlo,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New,monospace;
			font-style:normal;
			padding-left:3px;
		}
		optgroup {
			font-weight:bold;
			text-decoration:underline;
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
	
	<fieldset>
		<legend>Templates</legend>
		<select id="templates" class="multiColumnSelect" size="10">
			<optgroup label="Title;Path"></optgroup>
<?php
		$sql = "SELECT ID, Title, Path FROM Template ORDER BY Title";
		$rst = $db->Execute($sql);
		while(!$rst->EOF){
			echo '<option id="'.$rst['ID'].'">' . $rst['Title'].';'.$rst['Path'] . '</option>\n';
			$rst->MoveNext();
		}
?>
		</select>
	</fieldset>
	
	<fieldset>
		<legend>Instances</legend>
		<select id="instances" class="multiColumnSelect" size="10">
			<optgroup label="Title;Template"></optgroup>
<?php
		$sql = "SELECT Instance.ID, Instance.Title, Template.Title AS Template FROM Instance INNER JOIN Template ON Instance.Template = Template.ID ORDER BY Instance.Title, Template.Title";
		$rst = $db->Execute($sql);
		while(!$rst->EOF){
			echo '<option value="'.$rst['ID'].'">' . $rst['Title'].';'.$rst['Template'] . '</option>\n';
			$rst->MoveNext();
		}
?>
		</select>
	</fieldset>
	
	<fieldset>
		<legend>Settings</legend>
		<select id="settings" class="multiColumnSelect" size="10">
			<optgroup label="Key;Value"></optgroup>
<?php
		$sql = "SELECT ID, Title, Settings FROM Instance ORDER BY Title";
		$rst = $db->Execute($sql);
		while(!$rst->EOF){
			$settingsArr = json_decode($rst['Settings'], true);
			ksort($settingsArr);	//sort alphabetically by key
			foreach($settingsArr as $key => $value){
				echo '<option id="'.$rst['ID'].'">' . $key.';'.$value . '</option>\n';
			}
			$rst->MoveNext();
		}
?>
		</select>
	</fieldset>
	
	<script type="text/javascript">
		multiColumnSelect(";", "\u00a0\u00a0\u00a0\u00a0");
	</script>
<?php
		$rst->Close();
		$db->Close();
	}
?>
</body>
</html>
