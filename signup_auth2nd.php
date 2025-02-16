<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("signup_auth2nd");
	load_messagecatalog("app", $setting["web"]["lang"]);
	log_info("load_setting: success.");

	if( $_SERVER['REQUEST_METHOD']  == "HEAD" ) throw new ErrorException("access_from_bot");
	if( $_SERVER['HTTP_USER_AGENT'] == "" )     throw new ErrorException("access_from_bot");

	validate_inputs();
	$username = $_GET["username"];
	$sessionkey = $_GET["sessionkey"];
	log_info("validate_inputs: success.", ["username" => $username, "sessionkey" => $sessionkey]);

	$acct = load_account($username, false, true);
	if( !$acct ) throw new ErrorException("error_in_load_account");
	log_info("load_account: success.", ["username" => $username, "acct" => $acct]);

	$acct_sessionkey = $acct["sessionkey"];
	if( $acct_sessionkey != $sessionkey ) throw new ErrorException("unmatch_session_key");

	$now = time();
	$expiration_limit = $acct["creationtime"] + $setting["web"]["expiration_min_of_issuance"] * 60;
	if( $now > $expiration_limit ) throw new ErrorException("expired_issuance");

	$r = validate_account($username);
	if( !$r ) throw new ErrorException("error_in_validate_account");
	log_info("validate_acccount: success.", ["username" => $username]);

	$url = generate_otpauth_url($username, $setting, $acct);
	log_info("generate_otpauth_url: success.", ["username" => $username, "url" => $url]);

	$svg = generate_qrcode($url);

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

include("templates/signup_auth2nd.tmpl");


