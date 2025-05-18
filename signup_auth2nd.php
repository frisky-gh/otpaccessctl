<?php

// load libraries
require("lib/common.php");

$language_selector = null;
try{
	$setting = load_setting();
	set_channel_of_log("signup_auth2nd");
	log_info("load_setting: success.");

	if( $_SERVER['REQUEST_METHOD']  == "HEAD" ) throw new ErrorException("access_from_bot");
	if( $_SERVER['HTTP_USER_AGENT'] == "" )     throw new ErrorException("access_from_bot");

	validate_inputs();
	$username = $_POST["username"];
	$sessionkey = $_POST["sessionkey"];
	log_info("validate_inputs: success.", ["username" => $username, "sessionkey" => $sessionkey]);

	$lang = $_COOKIE["lang"] ?? $setting["web"]["default_lang"];
	$language_selector = generate_language_selector( $lang, $setting["web"]["lang_list"] );
	load_messagecatalog("app", $lang);
	log_info("load_messagecatalog: success.", ["lang" => $lang]);

	$acct = load_account($username, false, true);
	if( !$acct ) throw new ErrorException("error_in_load_account");
	log_info("load_account: success.", ["username" => $username, "acct" => $acct]);

	$acct_sessionkey = $acct["sessionkey"];
	if( $acct_sessionkey != $sessionkey ) throw new ErrorException("unmatch_session_key");

	$now = time();
	$expiration_limit = $acct["creationtime"] + $setting["web"]["expiration_min_of_registration"] * 60;
	if( $now > $expiration_limit ) throw new ErrorException("expired_registration");

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


