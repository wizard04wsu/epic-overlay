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
	
    <meta name="google-signin-scope" content="profile email">
    <meta name="google-signin-client_id" content="977450567667-btt3bsju6boeg0hdcqjl9n8dv5s2s1s7.apps.googleusercontent.com">
    <script src="https://apis.google.com/js/platform.js" defer></script>
	
	<style type="text/css" media="all">
		body {
			font-family:Arial, Helvetica, sans-serif;
		}
	</style>
	
</head>
<body>
	
	<div style="text-align:center; margin-bottom:2em;">
		<h1 style="margin-bottom:0.1em;">Epic Overlay</h1>
		<p style="font-size:80%; margin-top:0;">HTML overlays for use in the Open Broadcaster Software CLR browser</p>
	</div>
	
	<div class="g-signin2" data-onsuccess="onSignIn" data-theme="dark" style="margin:auto; display:table;"></div>
	<script>
		function onSignIn(googleUser) {
			// Useful data for your client-side scripts:
			var profile = googleUser.getBasicProfile();
			console.log("ID: " + profile.getId()); // Don't send this directly to your server!
			console.log("Name: " + profile.getName());
			console.log("Image URL: " + profile.getImageUrl());
			console.log("Email: " + profile.getEmail());
			
			// The ID token you need to pass to your backend:
			var id_token = googleUser.getAuthResponse().id_token;
			console.log("ID Token: " + id_token);
			
			//send the ID token to your server with an HTTPS POST request
			var xhr = new XMLHttpRequest();
			xhr.open('POST', 'https://epicstreamman.com/secure/epic-overlay/gsi/tokensignin.php');
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.onload = function() {
				console.log('Token sign-in response: ' + xhr.responseText);
				if(xhr.responseText == "success"){
					console.log("yay!");
					window.location.assign("http://epicstreamman.com/epic-overlay/dashboard.php");
				}
				else{
					console.log("boo...");
					//...
				}
			};
			xhr.send('id_token=' + id_token);
			//... show something while waiting for a response from the server
		};
	</script>
	
</body>
</html>
