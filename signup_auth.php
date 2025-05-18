<?php

// load libraries
require("lib/common.php");

$location = "signup_verify.php";

try{
	$setting = load_setting();
	set_channel_of_log("signup_auth");
	log_info("load_setting: success.");

	validate_inputs();
	$username = $_POST["username"];
	$password = $_POST["password"];
	if( empty($username) ) throw new ErrorException("empty_username");
	log_info("validate_inputs: success.", ["username" => $username]);

	$r = get_mailaddress_of_user( $setting, $username, $password );
	if( isset($r["error"]) ) throw new ErrorException($r["error"]);
	$mail = $r["result"];

	$acct = load_account($username, true, true);
	if( $acct ){
		// TODO: Consecutive issues shall be considered error.
	}

	$totpsecret = generate_totpsecret();
	$sessionkey = generate_sessionkey();
	$r = remove_account( $username );
	if( !$r ) throw new ErrorException("error_in_remove_account");
	log_info("remove_account: success.", ["username" => $username]);

	$r = store_account( $username, $totpsecret, $sessionkey );
	if( !$r ) throw new ErrorException("error_in_store_account");
	log_info("store_account: success.", ["username" => $username, "sessionkey" => $sessionkey]);

	$r = send_mail_at_account_registration( $setting, $mail, $username, $sessionkey );
	if( !$r ) throw new ErrorException("error_in_send_mail_at_account_registration");
	log_info("send_mail_at_account_registration: success.", ["username" => $username, "sessionkey" => $sessionkey, "mail" => $mail]);

	$location = "signup_verify.php";

}catch(Exception $e) {
	$message = $e->getMessage();
	print "<div>捕捉した例外: {$message}</div>\n";
	log_info("exception: catch.", ["message" => $message]);
	$location = "signup.php?message={$message}";
} finally {
}

header("Location: $location");


