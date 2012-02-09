<?php 
require_once "pass.php";

$users = array(
	"admin" => "adminpass"
);

function credentials_valid($username, $password){
	global $users;
	global $roomusers;
	return ((isset($users[$username]) && $users[$username] == $password)
		|| (isset($roomusers[$username]) && $roomusers[$username] == $password));
}

function log_in($user){
	$_SESSION['user'] = $user;
}

function current_user(){
	return isset($_SESSION['user'])?$_SESSION['user']:false;
}

function require_login(){
	if(!current_user()){
		$_SESSION['redirect_to'] = $_SERVER["REQUEST_URI"];
		header("Location: ../main.php?login_required=1");
		exit('Du är inte inloggad!');
	}
}
?>