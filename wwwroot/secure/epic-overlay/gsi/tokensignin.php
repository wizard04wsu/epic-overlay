<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header('Cache-Control: no-store, no-cache, max-age=0');
header('Expires: -1');

//see:
//https://developers.google.com/identity/sign-in/web/
//https://developers.google.com/identity/sign-in/web/backend-auth
//https://console.developers.google.com/apis/credentials?project=epic-overlay

$id_token; $response; $token_claims;
$googleID; $email; $name; $picture;
/*$dbPath;*/ $db; $cmd; $rst;

$client_id = '977450567667-btt3bsju6boeg0hdcqjl9n8dv5s2s1s7.apps.googleusercontent.com';

$_SESSION['user_id'] = NULL;


//######################################
// Verify the integrity of the ID token
//######################################

if(!isset($_POST['id_token'])){
	//no ID token given
	//...
	echo 'no ID token given';
	exit();
}

$id_token = $_POST['id_token'];

//validate the ID token using the Google tokeninfo endpoint
function validate_token($id_token){
	$ch; $response;
	
	// Initialize session and set URL.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token='.$id_token);
	
	// Set so curl_exec returns the result instead of outputting it.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	// Configure cURL to trust the certificate authority used by Google.
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	//got this cert bundle from 
	// http://curl.haxx.se/docs/caextract.html
	// under "RSA-1024 removed"
	curl_setopt($ch, CURLOPT_CAINFO, getcwd().'\ca-bundle-old.crt');
	
	// Get the response and close the channel.
	$response = curl_exec($ch);
	curl_close($ch);
	
	return $response;
}

//validate the ID token using the tokeninfo endpoint
$response = validate_token($id_token);
if(!$response){
	//no response
	//...
	echo 'no response';
	exit();
}
$token_claims = json_decode($response, true);
if(!isset($token_claims['aud'])){
	//invalid ID token
	//...
	echo 'invalid ID token';
	exit();
}
if($token_claims['aud'] != $client_id){
	//token is not intended for this client
	//...
	echo 'token is not intended for this client';
	exit();
}


//######################################
// Process the user's info
//######################################

//get the user's unique Google ID
$googleID = $token_claims['sub'];

//get the user's profile info
$_SESSION['email'] = $token_claims['email'];
$_SESSION['name'] = $token_claims['name'];
$_SESSION['picture'] = $token_claims['picture'];

//connect to the database
require '../../../epic-overlay/inc/dbPath.php';
if(!file_exists($dbPath)){
	echo 'Could not find the database file.';
	exit();
}
$db = new COM('ADODB.Connection');
$db->Open("Provider=Microsoft.ACE.OLEDB.12.0; Data Source=$dbPath");

//determine if the user is already in the database
$cmd = new COM('ADODB.Command');
$cmd->ActiveConnection = $db;
$cmd->CommandType = 1;	//adCmdText
$cmd->CommandText = 'SELECT ID FROM [User] WHERE GoogleID = ?';
$rst = $cmd->Execute($_, array($googleID));

if($rst->EOF){	//user does not exist
	
	//add the user
	$cmd = new COM('ADODB.Command');
	$cmd->ActiveConnection = $db;
	$cmd->CommandType = 1;	//adCmdText
	$cmd->CommandText = 'INSERT INTO [User] SELECT ? AS GoogleID, ? AS DisplayName, ? AS Email, ? AS Picture';
	$cmd->Execute($_, array($googleID, $_SESSION['name'], $_SESSION['email'], $_SESSION['picture']));
	
	//get the newly created user ID
	$rst = new COM('ADODB.Recordset');
	$rst->Open('SELECT @@IDENTITY', $db);
	$_SESSION['user_id'] = $rst[0]->Value;
	
}
else{	//user already exists
	
	$_SESSION['user_id'] = $rst['ID']->Value;
	
	//update the user's info
	$cmd = new COM('ADODB.Command');
	$cmd->ActiveConnection = $db;
	$cmd->CommandType = 1;	//adCmdText
	$cmd->CommandText = 'UPDATE [User] SET DisplayName = ?, Email = ?, Picture = ? WHERE GoogleID = ?';
	$cmd->Execute($_, array($_SESSION['name'], $_SESSION['email'], $_SESSION['picture'], $googleID));
	
}

//close the database connection
$rst->Close();
$db->Close();


echo 'success';

?>