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
	$sessionkey = $_POST["sessionkey"];
	log_info("validate_inputs: success.", ["sessionkey" => $sessionkey]);

	$pass = load_pass($sessionkey, false);
	if( !$pass ) throw new ErrorException("error_in_load_pass");
	log_info("load_pass: success.", ["sessionkey" => $sessionkey, "pass" => $pass]);

	$now = time();
	$issuance_expiration_date = $pass["creationtime"] + $setting["web"]["expiration_min_of_issuance"] * 60;
	$pass_expiration_date     = $pass["creationtime"] + $setting["cron"]["lifetime_min_of_pass"] * 60;
	if( $now > $issuance_expiration_date ) throw new ErrorException("expired_issuance");

	if( $setting["web"]["enable_signout_mail"] ){
		$username = $pass["username"];
		$mail     = $pass["mail"];
		$ipaddr   = $pass["ipaddr"];
      		$r = send_mail_for_signout( $setting, $mail, $username, $sessionkey, $ipaddr, $pass_expiration_date );
		if( !$r ) throw new ErrorException("error_in_send_mail_for_signout");
		log_info("send_mail_for_signout: success.", ["username" => $username, "sessionkey" => $sessionkey, "ipaddr" => $ipaddr, "mail" => $mail]);
	}

	$r = validate_pass($sessionkey);
	if( !$r ) throw new ErrorException("error_in_validate_pass");
	log_info("validate_pass: success.", ["sessionkey" => $sessionkey]);

	$url_directory = dirname($_SERVER['REQUEST_URI']);
	setcookie("sessionkey4signout", $sessionkey, $pass_expiration_date, $url_directory);
	log_info("setcookie: set sessionkey4signout cookie.", ["sessionkey" => $sessionkey, "path" => $url_directory, "expiration_at" => date("Y-m-d H:i:s", $pass_expiration_date)]);

	$location = "signin_complete.php";

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
	$location = "signin.php?message=auth2nd";

} finally {
}

header("Location: $location");

