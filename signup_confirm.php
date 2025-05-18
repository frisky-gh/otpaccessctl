<?php

// load libraries
require("lib/common.php");

$language_selector = null;
try{
	$setting = load_setting();
	set_channel_of_log("signup_confirm");
	log_info("load_setting: success.");

	if( $_SERVER['REQUEST_METHOD']  == "HEAD" ) throw new ErrorException("access_from_bot");
	if( $_SERVER['HTTP_USER_AGENT'] == "" )     throw new ErrorException("access_from_bot");

	validate_inputs();
	$username = $_GET["username"];
	$sessionkey = $_COOKIE["sessionkey"];
	log_info("validate_inputs: success.", ["username" => $username, "sessionkey" => $sessionkey]);

	$lang = $_COOKIE["lang"] ?? $setting["web"]["default_lang"];
	$language_selector = generate_language_selector( $lang, $setting["web"]["lang_list"] );
	load_messagecatalog("app", $lang);
	log_info("load_messagecatalog: success.", ["lang" => $lang]);

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
	header("Location: resources/forbidden.html");
	exit(0);
} finally {
}

include("templates/signup_confirm.tmpl");

