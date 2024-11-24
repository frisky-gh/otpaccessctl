<?php

// load libraries
require("lib/common.php");

$location = "signin.php?message=auth";

try{
	$setting = load_setting();
	set_channel_of_log("signin_auth");
	log_info("load_setting: success.");

	validate_inputs();
	$username = $_POST["username"];
	$password = $_POST["password"];
	$token = $_POST["token"];
	log_info("validate_inputs: success.", ["username" => $username, "token" => $token]);

	if     ( $setting["web"]["auth_method"] == "maildomain" ){
		$mail = $username . "@" . $setting["maildomain"]["domain"];

	}elseif( $setting["web"]["auth_method"] == "ldap" ){
		$mail = auth_by_ldap($setting, $username, $password);
		if( !$mail ) throw new ErrorException("auth_by_ldap");
		log_info("auth_by_ldap: success.", ["username" => $username, "mail" => $mail]);

	}else{
		throw new ErrorException("unknown_auth_method");
	}

	$acct = load_account( $username );
	if( !$acct ) throw new ErrorException("load_account");
	log_info("load_account: success.", ["acct" => $acct]);

	$server_token = generate_token( $setting, $acct );
	if( !$server_token ) throw new ErrorException("generate_token");
	log_info("generate_token: success.", ["server_token" => $server_token]);

	if( $token != $server_token ) throw new ErrorException("match_token");
	log_info("match_token: success.", ["token" => $token]);

	$sessionkey = generate_sessionkey();
	$ipaddr = $_SERVER['REMOTE_ADDR'];

	if     ( $setting["web"]["auth_method"] == "maildomain" ){
		$r = store_pass($sessionkey, $username, $ipaddr, false);
		if( !$r ) throw new ErrorException("store_pass");
		log_info("store_pass: success.", ["username" => $username, "sessionkey" => $sessionkey, "ipaddr" => $ipaddr]);

		$r = send_mail_at_pass_issuance( $setting, $mail, $username, $sessionkey );
		if( !$r ) throw new ErrorException("send_mail_at_pass_issuance");
		log_info("send_mail_at_pass_issuance: success.", ["username" => $username, "sessionkey" => $sessionkey, "ipaddr" => $ipaddr, "mail" => $mail]);

		$location = "signin_verify.php";

	}elseif( $setting["web"]["auth_method"] == "ldap" ){
		$r = store_pass($sessionkey, $username, $ipaddr, true);
		if( !$r ) throw new ErrorException("store_pass");
		log_info("store_pass: success.", ["username" => $username, "sessionkey" => $sessionkey, "ipaddr" => $ipaddr]);
		$location = "signin_complete.php?sessionkey={$sessionkey}";

	}else{
		throw new ErrorException("unknown_auth_method");
	}


}catch(Exception $e) {
	$message = $e->getMessage();
	print "<div>捕捉した例外: {$message}</div>\n";
	log_info("exception: catch.", ["message" => $message]);
	$location = "signin.php?message={$message}";
} finally {
}

header("Location: $location");


