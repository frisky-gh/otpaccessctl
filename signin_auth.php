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
	if( empty($username) ) throw new ErrorException("empty_username");
	if( empty($token) )    throw new ErrorException("empty_token");
	log_info("validate_inputs: success.", ["username" => $username, "token" => $token]);

	$r = get_mailaddress_of_user( $setting, $username, $password );
	if( isset($r["error"]) ) throw new ErrorException($r["error"]);
	$mail = $r["result"];

	$acct = load_account( $username );
	if( !$acct ) throw new ErrorException("unmatch_username_or_token");
	log_info("load_account: success.", ["acct" => $acct]);

	$server_token = generate_token( $setting, $acct );
	if( !$server_token ) throw new ErrorException("unmatch_username_or_token");
	log_info("generate_token: success.", ["server_token" => $server_token]);

	if( $token != $server_token ) throw new ErrorException("unmatch_username_or_token");
	log_info("match_token: success.", ["token" => $token]);

	$sessionkey = generate_sessionkey();
	$ipaddr = $_SERVER['REMOTE_ADDR'];

	if     ( $setting["web"]["auth_method"] == "maildomain" || $setting["web"]["auth_method"] == "mailaddress" ){
		$r = store_pass($sessionkey, $username, $ipaddr, $mail, false);
		if( !$r ) throw new ErrorException("error_in_store_pass");
		log_info("store_pass: success.", ["username" => $username, "sessionkey" => $sessionkey, "ipaddr" => $ipaddr]);

		$r = send_mail_at_pass_issuance( $setting, $mail, $username, $sessionkey, $ipaddr );
		if( !$r ) throw new ErrorException("error_in_send_mail_at_pass_issuance");
		log_info("send_mail_at_pass_issuance: success.", ["username" => $username, "sessionkey" => $sessionkey, "ipaddr" => $ipaddr, "mail" => $mail]);

		$location = "signin_verify.php?ipaddr={$ipaddr}";

	}elseif( $setting["web"]["auth_method"] == "ldap" ){
		$r = store_pass($sessionkey, $username, $ipaddr, $mail, true);
		if( !$r ) throw new ErrorException("error_in_store_pass");
		log_info("store_pass: success.", ["username" => $username, "sessionkey" => $sessionkey, "ipaddr" => $ipaddr]);

		$url_directory = dirname($_SERVER['REQUEST_URI']);
		setcookie("sessionkey", $sessionkey, time() + 3600, $url_directory);
		$location = "signin_complete.php?ipaddr={$ipaddr}";

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


