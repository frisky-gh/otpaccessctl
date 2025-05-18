<?php

// load libraries
require("lib/common.php");

$location = "signup_confirm.php";

try{
	$setting = load_setting();
	set_channel_of_log("signup_setcookie");
	log_info("load_setting: success.");

	if( $_SERVER['REQUEST_METHOD']  == "HEAD" ) throw new ErrorException("access_from_bot");
	if( $_SERVER['HTTP_USER_AGENT'] == "" )     throw new ErrorException("access_from_bot");

	validate_inputs();
	$sessionkey = $_GET["sessionkey"];
	$username   = $_GET["username"];

	log_info("validate_inputs: success.", ["sessionkey" => $sessionkey, "username" => $username]);

	if( !isset($sessionkey) ) throw new ErrorException("invalid_sessionkey");

	$url_directory = dirname($_SERVER['REQUEST_URI']);
	setcookie("sessionkey", $sessionkey, time() + 3600, $url_directory);
	log_info("setcookie: set sessionkey cookie.", ["sessionkey" => $sessionkey, "path" => $url_directory]);

	$location = "signup_confirm.php?username=$username";

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
	$location = "resources/servererror.html";

} finally {
}

header("Location: $location");

