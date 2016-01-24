<!DOCTYPE html>

<html lang="en">
<head>
	
	<!-- to prevent IE from using compatibility mode -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	
	<meta charset="UTF-8">
	
	<title>keyboard event values</title>
	
    <meta name="google-signin-scope" content="profile email">
    <meta name="google-signin-client_id" content="977450567667-btt3bsju6boeg0hdcqjl9n8dv5s2s1s7.apps.googleusercontent.com">
    <script src="https://apis.google.com/js/platform.js" defer></script>
	
</head>
<body>
	
	<div class="g-signin2" data-onsuccess="onSignIn" data-theme="dark"></div>
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
			/*var xhr = new XMLHttpRequest();
			xhr.open('POST', 'https://epicstreamman.com/tokensignin.php');
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.onload = function() {
				console.log('Signed in as: ' + xhr.responseText);
			};
			xhr.send('id_token=' + id_token);*/
		};
	</script>
	
	<a href="#" onclick="signOut();">Sign out</a>
	<script>
		function signOut() {
			var auth2 = gapi.auth2.getAuthInstance();
			auth2.signOut().then(function () {
				console.log('User signed out.');
			});
		}
	</script>
	
</body>
</html>
