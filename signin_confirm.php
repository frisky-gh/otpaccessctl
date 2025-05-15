<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("signin_confirm");
	load_messagecatalog("app", $setting["web"]["lang"]);
	log_info("load_setting: success.");

	if( $_SERVER['REQUEST_METHOD']  == "HEAD" ) throw new ErrorException("access_from_bot");
	if( $_SERVER['HTTP_USER_AGENT'] == "" )     throw new ErrorException("access_from_bot");

	validate_inputs();
	$sessionkey = $_GET["sessionkey"];
	log_info("validate_inputs: success.", ["sessionkey" => $sessionkey]);

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
	header("Location: resources/servererror.html");
	exit(0);

} finally {
}

include("templates/signin_confirm.tmpl");

