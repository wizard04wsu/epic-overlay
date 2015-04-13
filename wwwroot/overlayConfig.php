<?php
//declare variables (just for my sanity)
$errMsg = '';
$dbPath; $db; $sql; $rst;
$settings; $key; $value;


//connect to the database
$dbPath = realpath($_SERVER['DOCUMENT_ROOT'].'/../data/overlayConfig.accdb');
if(!file_exists($dbPath)){
	$errMsg = 'Could not find the database file.';
}
else{
	$db = new COM('ADODB.Connection');
	$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
}
	
?><!DOCTYPE html>

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
	</style>
	
</head>
<body>
	
	<h1>OBS Overlays Configuration</h1>
	
	<fieldset>
		<legend>Templates</legend>
		<table id="templates">
			<thead><tr><th>Title</th><th>Path</th></tr></thead>
			<tbody>
<?php
	$sql = "SELECT Title, Path FROM Template ORDER BY Title";
	$rst = $db->Execute($sql);
	while(!$rst->EOF){
		echo '<tr><td>' . $rst['Title']->Value . '</td><td>' . $rst['Path']->Value . '</td></tr>';
		$rst->MoveNext();
	}
?>
		</tbody></table>
	</fieldset>
	
	<fieldset>
		<legend>Instances</legend>
		<table id="instances">
			<thead><tr><th>ID</th><th>Title</th><th>Template</th><th>Link</th></tr></thead>
			<tbody>
<?php
	$sql = "SELECT Instance.ID, Instance.Title, Template.Title AS Template, Template.Path FROM Instance INNER JOIN Template ON Instance.Template = Template.ID ORDER BY Instance.Title, Template.Title";
	$rst = $db->Execute($sql);
	while(!$rst->EOF){
		echo '<tr><td>' . $rst['ID']->Value . '</td><td>' . $rst['Title']->Value . '</td><td>' . $rst['Template']->Value . '</td><td><a href="' . $rst['Path']->Value.'?instance='.$rst['ID'] . '" target="_blank">Open</a></td></tr>';
		$rst->MoveNext();
	}
?>
		</tbody></table>
	</fieldset>
	
	<fieldset>
		<legend>Settings</legend>
		<table id="instances">
			<thead><tr><th>Instance ID</th><th>Key</th><th>Value</th></tr></thead>
			<tbody>
<?php
	$sql = "SELECT ID, Title, Settings FROM Instance ORDER BY Title";
	$rst = $db->Execute($sql);
	while(!$rst->EOF){
		$settings = json_decode($rst['Settings'], true);
		ksort($settings);	//sort alphabetically by key
		foreach($settings as $key => $value){
			echo '<tr><td>' . $rst['ID']->Value . '</td><td>' . $key . '</td><td>' . $value . '</td></tr>\n';
		}
		$rst->MoveNext();
	}
?>
		</tbody></table>
	</fieldset>
	
</body>
</html>
<?php
	$rst->Close();
	$db->Close();
?>