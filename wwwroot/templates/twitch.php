<?php
error_reporting(E_ALL);
//ini_set('display_errors', 0);

header("Cache-Control: no-store, no-cache, max-age=0");
header("Expires: -1");

require '_getSettings.php';

$volume; $video;

if(!$errMsg){
	
	$volume = intval($settingsArr['volume']);
	$volume = $volume < 0 ? 0 : $volume > 100 ? 100 : $volume;
	
	$video = $settingsArr['video'] ? '&videoId='.$settingsArr['video'] : '';
	
}

?><!DOCTYPE html>

<html>
<head>
	
	<meta charset="UTF-8">
	
	<title><?php echo $title ? $title : 'Epic Overlay: Twitch player'; ?></title>
	
	<style type="text/css" media="all">
		html, body {
			margin: 0;
			padding: 0;
		}
		html {
			height:100%;
		}
		body {
			position:absolute;
			top:0;
			right:0;
			bottom:-31px;
			left:0;
			overflow:hidden;
		}
		#clear {
			position:absolute;
			top:0;
			right:0;
			bottom:0;
			left:0;
		}
	</style>
	
</head>
<body>
<?php
if($errMsg){
	echo $errMsg;
}
else{
?>
	<object id="player" type="application/x-shockwave-flash" 
			height="100%" 
			width="100%" 
			data="http://www.twitch.tv/widgets/live_embed_player.swf" 
			bgcolor="#000000"
			>
		<param name="wmode" value="opaque">	<!--so it stops displaying on top of stuff-->
		<param name="allowFullScreen" 
			value="false">
		<param name="allowScriptAccess" 
			value="always">
		<param name="allowNetworking" 
			value="all">
		<param name="movie" 
			value="http://www.twitch.tv/widgets/live_embed_player.swf">
		<param name="flashvars" 
			value="channel=<?php echo $settingsArr['channel'] . $video; ?>&auto_play=true&start_volume=<?php echo $volume; ?>">
	</object>
	<div id="clear"></div>
	
	<script type="text/javascript">
		var timer, clear = document.getElementById("clear");
		clear.addEventListener("mousemove", showControls, false);
		function showControls(evt){
			document.body.style.bottom = 0;
			clear.style.display = "none";
			timer = setTimeout(hideControls, 3000);
		}
		function hideControls(evt){
			document.body.style.bottom = "";
			clear.style.display = "";
		}
	</script>
<?php
}
?>
</body>
</html>
