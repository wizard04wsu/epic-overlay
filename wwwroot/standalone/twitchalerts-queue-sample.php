<!DOCTYPE html>

<html>
<head lang="en">
	
	<meta charset="UTF-8">
	
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	
	<title>TwitchAlerts On-Demand Sample Alert</title>
	
</head>
<body>
	
	<p>
	<a href="http://www.twitchalerts.com/dashboard" target="_blank">Make sure you are logged into TwitchAlerts</a>
	</p>
	
	<!--<button id="go">Send request</button>-->
	
	<p style="line-height:1.5em;">
	<label>Alert type 
	<select id="genType">
		<option value="follower" id="genType-follower">Follow</option>
		<option value="subscriber" id="genType-subscriber">Subscription</option>
		<option value="donator" id="genType-donator">Donation</option>
		<option value="host" id="genType-host">Hosting</option>
	</select>
	</label><br>
	<label>Variation number <input type="number" id="genVariation" step="1" min="0" value="0"></label> (0 for default)<br>
	<button id="genLink" onclick="generateLink();">Generate Link</button>
	</p>
	<p id="link"></p>
	
	<iframe id="url" style="display:none;"></iframe>
	
	<script type="text/javascript">
		
		var btn = document.getElementById("go"),
			iframe = document.getElementById("url"),
			qs = document.location.search.slice(1).split("&"),
			i,
			pair,
			type,
			variation;
		
		for(i=0; i<qs.length; i++){
			pair = qs[i].split("=");
			if(pair[0] === "type"){
				type = pair[1];
			}
			else if(pair[0] === "variation"){
				variation = pair[1];
			}
		}
		
		
		
		//btn.addEventListener("click", sendAlert, false);
		sendAlert();
		
		function sendAlert(){
			if(document.getElementById("genType-"+type) && (variation === "0" || 1*variation)){
				iframe.src = "http://www.twitchalerts.com/service/dashboard/queue-sample-"+type+"?variation="+encodeURIComponent(variation-1);
			}
		}
		
		
		
		if(type) document.getElementById("genType-"+type).selected = true;
		if(variation) document.getElementById("genVariation").value = variation;
		
		function generateLink(){
			
			var l = document.location
				type = document.getElementById("genType").value,
				variation = document.getElementById("genVariation").value,
				url = l.protocol+'//'+l.host+l.pathname+'?type='+type+'&variation='+variation;
			
			document.getElementById("link").innerHTML = '<a href="'+url+'">'+url+'</a>';
			
		}
		
	</script>
	
</body>
</html>
