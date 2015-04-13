<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/

//declare variables (just for my sanity)
$errMsg = '';
$instance = null;
$dbPath = '';
$db = null;
$cmd = null;
$_;	//placeholder variable (need a variable to pass to $cmd->Execute(), but I don't care what gets put into it)
$sql = '';
$rst = null;
$settings = [];


if(empty($_GET['instance']) || !intval($_GET['instance'])){
	$errMsg = 'Instance number is not specified.';
}
else{
	
	$instance = intval($_GET['instance']);
	
	$dbPath = realpath($_SERVER['DOCUMENT_ROOT'].'/../data/overlayConfig.accdb');
	if(!file_exists($dbPath)){
		$errMsg = 'Could not find the database file.';
	}
	else{
		
		$db = new COM('ADODB.Connection');
		$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");
		
		//make sure the instance number corresponds to an instance of this template
		$cmd = new COM('ADODB.Command');
		$cmd->ActiveConnection = $db;
		$cmd->CommandText = 'SELECT * FROM Instance INNER JOIN Template ON Instance.Template = Template.ID ' .
							'WHERE Template.Path = ? AND Instance.ID = ?';
		$cmd->CommandType = 1;	//adCmdText
		$rst = $cmd->Execute($_, array($_SERVER['URL'], $instance));
		
		
		if($rst->EOF){
			$errMsg = 'Specified instance does not use this template.';
		}
		else{

			$sql = "SELECT Option.Key, Setting.Value FROM Setting INNER JOIN [Option] ON Setting.Option = Option.ID WHERE Instance = $instance";

			$rst = $db->Execute($sql);

			while(!$rst->EOF){
				$settings[$rst['Key']->Value] = $rst['Value']->Value;
				
				$rst->MoveNext();
			}
			
			$rst->Close();
			$db->Close();
			
		}
		
	}
	
}
	
?><!DOCTYPE html>

<html>
<head>
	
	<meta charset="UTF-8">
	
	<title>Epic Stream Man's YouTube Player</title>
	
	<style type="text/css" media="all">
		html, body {
			margin: 0;
			padding: 0;
			/*font-size: 0;	/*to make sure there's no extra whitespace being rendered*/
		}
		html, body, #ytplayer {
			width: 100%;
			height: 100%;
		}
	</style>
	
	<script type="text/javascript">
		
		var settings = <?php echo json_encode($settings); ?>;
		
		function initPlayer(){
			
			"use strict";
			
			var tag, firstScriptTag, player,
				playOrder = [], playOrderIndex, isPlaying;
			
			//Load the IFrame Player API code asynchronously.
			tag = document.createElement('script');
			tag.src = "https://www.youtube.com/iframe_api";
			firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
			
			//Once the API script loads, it will call this function.
			window.onYouTubeIframeAPIReady = function (){
				
				//create the player, replacing div#ytplayer
				player = new YT.Player('ytplayer', {
						playerVars: { autohide: 1 },
						events: {
							'onReady': onPlayerReady,
							'onStateChange': onPlayerStateChange
						}
					});
				
				function onPlayerReady(event){
					//the player is ready
					
					//cue the playlist
					player.cuePlaylist({
							listType: settings.listType,	//e.g., "playlist"
							list: settings.list				//e.g., "PLfK-bTGowviuw8HkxaRZbJOUZdlKaouy2"
						});
					
					//set the volume
					player.setVolume(settings.volume);
				}
				
				function onPlayerStateChange(event){
					var videoCount, i;
					
					//console.log("State changed to: "+["unstarted","ended","playing","paused","buffering","","video cued"][event.data+1]);
					
					//Video has not yet started playing
					if(event.data == -1){
						
						isPlaying = false;	//don't pick another video until this one actually plays
						
					}
					
					//Playlist is cued
					else if(event.data == YT.PlayerState.CUED){
						
						//player.setShuffle(settings.shuffle);	//API bug -- this doesn't actually work
						//player.setLoop(settings.loop);	//useless since .setShuffle doesn't work
						
						//determine the order the videos will play in
						videoCount = player.getPlaylist().length;
						for(i=0; i<videoCount; i++){
							playOrder[i] = i;
						}
						if(settings.shuffle){
							playOrder = shuffle(playOrder);
						}
						playOrderIndex = 0;
						
						//play the first video
						player.playVideoAt(playOrder[playOrderIndex++]);
						
					}
					
					//Video is playing
					else if(event.data == YT.PlayerState.PLAYING){
						
						//console.log("Video #"+(player.getPlaylistIndex()+1)+" is playing");
						
						isPlaying = true;	//when this video ends, play a different one
						
					}
					
					//Video has ended
					else if(event.data == YT.PlayerState.ENDED){
						
						if(!isPlaying) return;	//workaround for API weirdness (ENDED before it was even PLAYING)
						
						//loop if needed
						if(settings.loop && playOrderIndex == playOrder.length){
							playOrderIndex = 0;
						}
						
						//play the next video
						player.playVideoAt(playOrder[playOrderIndex++]);
						
						isPlaying = false;	//don't pick another video until this one actually plays
						
					}
				}
				
				//http://stackoverflow.com/a/2450976
				function shuffle(array) {
					var currentIndex = array.length, temporaryValue, randomIndex;
					
					// While there remain elements to shuffle...
					while (0 !== currentIndex){
						
						// Pick a remaining element...
						randomIndex = Math.floor(Math.random() * currentIndex);
						currentIndex--;
						
						// And swap it with the current element.
						temporaryValue = array[currentIndex];
						array[currentIndex] = array[randomIndex];
						array[randomIndex] = temporaryValue;
					}
					
					return array;
				}
				
			}
			
		}
		
	</script>
	
</head>
<body>
<?php
	if($errMsg){
		echo $errMsg;
	}
	else{
?>	<!-- The <iframe> with the video player will replace this <div>. -->
	<div id="ytplayer"></div>
	<script type="text/javascript">initPlayer();</script>
	
<?php } ?></body>
</html>
