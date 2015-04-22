<?php
error_reporting(E_ALL);
//ini_set('display_errors', 0);

header("Cache-Control: no-store, no-cache, max-age=0");
header("Expires: -1");

require '_getSettings.php';

?><!DOCTYPE html>

<html>
<head>
	
	<meta charset="UTF-8">
	
	<title><?php echo $title; ?></title>
	
	<style type="text/css" media="all">
		html, body {
			margin: 0;
			padding: 0;
		}
		html, body, #player {
			width: 100%;
			height: 100%;
		}
		#player {
			display: block;
		}
	</style>
	
	<script type="text/javascript">
		
		var settings = <?php echo $settingsJson; ?>;
		
		function initPlayer(){
			
			"use strict";
			
			//
			
		}
		
	</script>
	
</head>
<body>
<?php
if($errMsg){
	echo $errMsg;
}
else{
?>
	<div id="player"></div>
	<script type="text/javascript">
		//
	</script>
<?php
}
?>
</body>
</html>
