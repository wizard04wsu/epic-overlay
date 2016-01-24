<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

session_start();

if($_SESSION['user_id']){
	header('Location: http://epicstreamman.com/epic-overlay/dashboard.php');
	exit();
}
?><!DOCTYPE html>

<html lang="en">
<head>
	
	<!-- to prevent IE from using compatibility mode -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	
	<meta charset="UTF-8">
	
	<title>Epic Overlay Sign-In</title>
	
	<script>if(window !== window.top) window.top.location.assign("https://epicstreamman.com/secure/epic-overlay/signIn.php");</script>
	
    <meta name="google-signin-scope" content="profile email">
    <meta name="google-signin-client_id" content="977450567667-btt3bsju6boeg0hdcqjl9n8dv5s2s1s7.apps.googleusercontent.com">
    <script src="https://apis.google.com/js/platform.js" defer></script>
	
	<script>
		function textToHTML(str){
			return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;")/*.replace(/'/g, "&#39;")*/.replace(/'/g, "&apos;");	//PHP uses &apos; instead of &#39;
		}
	</script>
	
	<style type="text/css" media="all">
		body {
			font-family:Arial, Helvetica, sans-serif;
		}
		
		#title {
			text-align:center;
			margin-bottom:2em;
		}
		#title h1 {
			margin-bottom:0.1em;
		}
		#title sup {
			color:red;
			font-weight:bold;
			font-style:italic;
			font-size:70%;
		}
		#title p {
			font-size:80%;
			margin-top:0;
		}
		
		#GoogleSignIn, #EpicSignIn {
			display:table;
			margin:auto;
			margin-bottom:2em;
			text-align:center;
		}
		#GoogleSignIn.signed-in #GSI_button {
			display:none;
		}
		#GSI_user .pic {
			display:inline-block;
			width:32px;
			height:32px;
			border-radius:50%;
			vertical-align:sub;
		}
	</style>
	
</head>
<body>
	
	<div id="title">
		<h1>Epic Overlay <sup>beta</sup></h1>
		<p>HTML overlays for use in the Open Broadcaster Software CLR browser</p>
	</div>
	
	<div id="GoogleSignIn">
		<div id="GSI_button" class="g-signin2" data-onsuccess="onGoogleSignIn" data-theme="dark"></div>
		<div id="GSI_user"></div>
	</div>
	
	<div id="EpicSignIn"></div>
	
	<script>
		(function (){
			
			var GSI = document.getElementById("GoogleSignIn"),
				googleUser,
				userInfo = document.getElementById("GSI_user"),
				epic = document.getElementById("EpicSignIn");
			
			this.onGoogleSignIn = function (user) {
				
				googleUser = user;
				
				// Useful data for your client-side scripts:
				var profile = googleUser.getBasicProfile();
				/*console.log("ID: " + profile.getId()); // Don't send this directly to your server!
				console.log("Name: " + profile.getName());
				console.log("Image URL: " + profile.getImageUrl());
				console.log("Email: " + profile.getEmail());*/
				
				userInfo.innerHTML = "You are signed into Google as<br>" +
					'<span class="pic" style="background-image:url('+ (profile.getImageUrl().replace(/\/s96-c\/photo.jpg$/, "/s32-c/photo.jpg")) + ')"></span> '+textToHTML(profile.getName());
				
				GSI.className = "signed-in";
				
				epic.innerHTML = '<a href="javascript:epicSignIn()">Sign into Epic Overlay</a>';
				
			}
			
			this.epicSignIn = function (){
				
				// The ID token you need to pass to your backend:
				var id_token = googleUser.getAuthResponse().id_token;
				/*console.log("ID Token: " + id_token);*/
				
				//send the ID token to your server with an HTTPS POST request
				var xhr = new XMLHttpRequest();
				xhr.open('POST', 'https://epicstreamman.com/secure/epic-overlay/gsi/tokensignin.php');
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				xhr.onload = function() {
					if(xhr.responseText == "success"){
						//success; go to the dashboard
						window.location.assign("http://epicstreamman.com/epic-overlay/dashboard.php");
					}
					else if(xhr.responseText == "invalid"){
						//user logged out of Google; reload the sign-in page
						window.location.reload();
					}
					else{
						//some other error; let the user know
						console.log('Token sign-in response: ' + xhr.responseText);
						epic.innerHTML = "We're having trouble signing you into Epic Overlay.<br>" +
							'Please <a href="javascript:epicSignIn()">try again</a>.';
					}
				};
				xhr.send('id_token=' + id_token);
				
				//show something to the user while waiting for a response from the server
				epic.innerHTML = "Please wait while you are signed into the Epic Overlay dashboard...";
				
			}
			
	   })();
	</script>
	
</body>
</html>
