<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

session_start();

if($_SESSION['user_id']){
	header('Location: dashboard.php');
}
else{
	header('Location: https://epicstreamman.com/secure/epic-overlay/signIn.php');
}
?>