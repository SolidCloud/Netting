<?php
session_start();
require_once "auth.php";
if(credentials_valid($_POST['username'], $_POST['password'])){
	log_in($_POST['username']);
	if($_SESSION['user']!="admin")
		header("Location: rum.php?rum=".str_replace(" ","_",$_SESSION["user"]));
	else if ($_SESSION['redirect_to']){
		header("Location: ". $_SESSION['redirect_to']);
		unset($_SESSION['redirect_to']);
	}
	else
		header("Location: index.php");
}
else {
	header("Location: ../main.php?error=1");
	exit("Fel användarnamn eller lösenord!");
}
?>