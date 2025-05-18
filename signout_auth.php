<?php

// load libraries
require("lib/common.php");

$location = "signin.php?message=signout_auth";

try{
	$setting = load_setting();
	set_channel_of_log("signout_auth");
	log_info("load_setting: success.");

	if( $_SERVER['REQUEST_METHOD']  == "HEAD" ) throw new ErrorException("access_from_bot");
	if( $_SERVER['HTTP_USER_AGENT'] == "" )     throw new ErrorException("access_from_bot");

	validate_inputs();
	$sessionkey = $_POST["sessionkey"];
	log_info("validate_inputs: success.", ["sessionkey" => $sessionkey]);

	$pass = load_pass($sessionkey, true, true);
	if( !$pass ) throw new ErrorException("error_in_load_pass");
	log_info("load_pass: success.", ["sessionkey" => $sessionkey, "pass" => $pass]);

	$r = expire_pass($sessionkey);
	if( !$r ) throw new ErrorException("error_in_expire_pass");
	log_info("expire_pass: success.", ["sessionkey" => $sessionkey]);

	$url_directory = dirname($_SERVER['REQUEST_URI']);
	setcookie("sessionkey", $sessionkey, time()+3600, $url_directory);
	setcookie("sessionkey4signout", $sessionkey, time()-1, $url_directory);
	log_info("setcookie: unset sessionkey4signout cookie.", ["sessionkey" => $sessionkey, "path" => $url_directory]);

	$location = "signout_complete.php";

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
	$location = "resources/servererror.html";

} finally {
}

header("Location: $location");

