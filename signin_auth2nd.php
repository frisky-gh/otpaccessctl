<?php

// load libraries
require("lib/common.php");

$location = "signin.php?message=auth2nd";

try{
	$setting = load_setting();
	set_channel_of_log("signin_auth2nd");
	log_info("load_setting: success.");

	if( $_SERVER['REQUEST_METHOD']  == "HEAD" ) throw new ErrorException("access_from_bot");
	if( $_SERVER['HTTP_USER_AGENT'] == "" )     throw new ErrorException("access_from_bot");

	validate_inputs();
	$sessionkey = $_GET["sessionkey"];
	log_info("validate_inputs: success.", ["sessionkey" => $sessionkey]);

	$pass = load_pass($sessionkey, false);
	if( !$pass ) throw new ErrorException("error_in_load_pass");
	log_info("load_pass: success.", ["sessionkey" => $sessionkey, "pass" => $pass]);

	$r = validate_pass($sessionkey);
	if( !$r ) throw new ErrorException("error_in_validate_pass");
	log_info("validate_pass: success.", ["sessionkey" => $sessionkey]);

	$location = "signin_complete.php?sessionkey={$sessionkey}";

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
	$location = "signin.php?message=auth2nd";

} finally {
}

header("Location: $location");

