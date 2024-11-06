<?php

// load libraries
require("lib/common.php");

$location = "signin_complete.php";

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
		$r = store_request($username, $sessionkey, $ipaddr);
		if( !$r ) throw new ErrorException("store_request");
		log_info("store_request: success.", ["username" => $username, "sessionkey" => $sessionkey, "ipaddr" => $ipaddr]);

	}elseif( $setting["web"]["auth_method"] == "ldap" ){
		$r = store_request($username, $sessionkey, $ipaddr);
		if( !$r ) throw new ErrorException("store_request");
		log_info("store_request: success.", ["username" => $username, "sessionkey" => $sessionkey, "ipaddr" => $ipaddr]);

	}else{
		throw new ErrorException("unknown_auth_method");
	}

	$location = "signin_complete.php?sessionkey={$sessionkey}";

}catch(Exception $e) {
	$message = $e->getMessage();
	print "<div>捕捉した例外: {$message}</div>\n";
	log_info("exception: catch.", ["message" => $message]);
	$location = "signin.php?message={$message}";
} finally {
}

header("Location: $location");


