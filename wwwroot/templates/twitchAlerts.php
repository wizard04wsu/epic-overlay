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
	
	<title><?php echo $title ? $title : 'Epic Overlay: Sample TwitchAlerts'; ?></title>
	
	<style type="text/css" media="all">
		h1 { font-size:1.5em; }
		h2 { font-size:1.25em; }
	</style>
	
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script type="text/javascript">
		var UPDATE_INTERVAL = 1000;	//milliseconds
	</script>
	
</head>
<body>
<?php
if($errMsg){
	echo $errMsg;
}
else{
?>
	<h1 style="font-size:1.25em;">TwitchAlerts On-Demand Sample Alert</h1>
	
	<p>
	<a href="http://www.twitchalerts.com/dashboard/alert-box-v3" target="_blank">Make sure you are logged into TwitchAlerts!</a>
	</p>
	
	<h2>Alert Link Generator</h2>
	
	<p>
	<label>Alert type 
	<select id="genType">
		<option value="follower" id="genType-follower">Follow</option>
		<option value="subscriber" id="genType-subscriber">Subscription</option>
		<option value="donator" id="genType-donator">Donation</option>
		<option value="host" id="genType-host">Hosting</option>
	</select>
	</label><br>
	<label>Variation number <input type="number" id="genVariation" step="1" min="0" value="0"></label> (0 for default)<br>
	<button id="testBtn">Test</button>
	<button id="genLink">Generate Link</button>
	</p>
	<p>
	<input type="text" id="link" contentEditable="false" style="width:50em;">
	</p>
	
	<iframe id="url" style="display:none;"></iframe>
	
	<script type="text/javascript">
		
		var settings = <?php echo $settingsJson; ?>,
			testBtn = document.getElementById("testBtn"),
			genBtn = document.getElementById("genLink"),
			iframe = document.getElementById("url");
		
		testBtn.addEventListener("click", function (evt){ sendAlert(); }, false);
		genBtn.addEventListener("click", generateLink, false);
		
		if(settings.queue.length){
			sendQueuedAlert();
		}
		else{
			setTimeout(sendQueuedAlert, UPDATE_INTERVAL);
		}
		
		function sendQueuedAlert(){
			
			//use jQuery to remove this alert from the queue
			$.ajax({
				url: "twitchAlerts_shift.php",
				method: 'GET',
				data: {
					instance: <?php echo $instance; ?>
				}
			}).done(function(content, message, xhr) {
				var o;
				
				try{
					o = JSON.parse(content);
				}catch(e){
					o = null;
				}
				
				if(o !== null){	//success
					if(o.alert) {	//alert info returned
						sendAlert(o.alert.type, o.alert.variation);
					}
				}
				else{
					//display the error message
					alert("Failed to remove alert from queue:\n\n"+content);
				}
				
				setTimeout(sendQueuedAlert, UPDATE_INTERVAL);
			}).fail(function(xhr, message, errorThrown) {
				//display a generic error message
				alert("Failed to remove alert from queue:\n\n"+message+"\n\n"+errorThrown);
				
				setTimeout(sendQueuedAlert, UPDATE_INTERVAL);
			});
			
		}
		
		function sendAlert(type, variation){
			var type = type || document.getElementById("genType").value,
				variation = variation || document.getElementById("genVariation").value;
			
			iframe.src = "http://www.twitchalerts.com/service/dashboard/queue-sample-"+type+"?variation="+(variation-1);
		}
		
		function generateLink(){
			var input = document.getElementById("link"),
				l = document.location,
				type = document.getElementById("genType").value,
				variation = document.getElementById("genVariation").value,
				url = l.protocol+'//'+l.host+l.pathname.replace(/\.php$/i, "_push.php")+'?instance=<?php echo $instance; ?>&type='+type+'&variation='+variation;
			
			input.value = url;
			input.select();
		}
		
	</script>
<?php
}
?>
</body>
</html>
