<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

session_start();

$_SESSION['user_id'] = NULL;

header('Location: ../signIn.php');
?>