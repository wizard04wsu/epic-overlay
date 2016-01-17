<?php
error_reporting(E_ALL);
//ini_set('display_errors', 0);

header("Cache-Control: no-store, no-cache, max-age=0");
header("Expires: -1");

//see:
//https://developers.google.com/identity/sign-in/web/
//https://developers.google.com/identity/sign-in/web/backend-auth
//https://console.developers.google.com/apis/credentials?project=epic-overlay

$id_token; $response; $token_claims; $user_id;

$client_id = '977450567667-btt3bsju6boeg0hdcqjl9n8dv5s2s1s7.apps.googleusercontent.com';

if(!isset($_POST['id_token'])){
	//no ID token given
	//...
	exit();
}

$id_token = $_POST['id_token'];

//#####################################//
//Verify the integrity of the ID token
//#####################################//

//validate the ID token using the tokeninfo endpoint
$response = http_get('https://www.googleapis.com/oauth2/v3/tokeninfo?id_token='.$id_token);
if(!$response){
	//invalid ID token
	//...
	exit();
}
$token_claims = json_decode($response, true);
if($token_claims->aud != $client_id){
	//token is not intended for this client
	//...
	exit();
}

//#####################################//
//
//#####################################//

//get the user's unique Google ID
$user_id = $token_claims->sub;

$_SESSION['signed_in'] = true;
//...

?>