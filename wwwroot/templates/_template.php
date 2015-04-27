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
	
	<title><?php echo $title ? $title : 'Epic Overlay: player'; ?></title>
	
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
	
	<!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>-->
	<script type="text/javascript">
		var UPDATE_INTERVAL = 10000;	//milliseconds
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
		var settings = <?php echo $settingsJson; ?>;
		
        setTimeout(checkForChanges, UPDATE_INTERVAL);
        
        function checkForChanges(){
            //check for changes to the instance's settings
			$.ajax({
				url: '../checkForChanges.php',
				method: 'GET',
				data: {
					instance: <?php echo json_encode($instance); ?>,
					timestamp: <?php echo json_encode($modified); ?>
				}
			}).done(function(content, message, xhr) {
				
                if(xhr.status == 204){
                    //success; no changes
                 }
                else if(xhr.status == 205){
                    //success; there are changes
                    //reload the page
                    window.location.reload(true);
                }
                else{
                    //error returned
					//ignore it
				}
                
                setTimeout(checkForChanges, UPDATE_INTERVAL);
				
			}).fail(function(xhr, message, errorThrown) {
				//generic error
				//ignore it
                setTimeout(checkForChanges, UPDATE_INTERVAL);
			})
        }
	</script>
<?php
}
?>
</body>
</html>
