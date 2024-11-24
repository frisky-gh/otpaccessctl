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
	log_info("validate_inputs: success.", ["username" => $username]);

	if     ( $setting["web"]["auth_method"] == "maildomain" ){ 
		$mail = $username . "@" . $setting["maildomain"]["domain"];

	}elseif( $setting["web"]["auth_method"] == "ldap" ){ 
		$mail = auth_by_ldap($setting, $username, $password);
		if( !$mail ) throw new ErrorException("auth_by_ldap");
		log_info("auth_by_ldap: success.", ["username" => $username, "mail" => $mail]);

	}else{ 
		throw new ErrorException("unknown_auth_method");
	}

	$acct = load_account($username, true, true);
	if( $acct ){
		// TODO: Consecutive issues shall be considered error.
	}

	$totpsecret = generate_totpsecret();
	$sessionkey = generate_sessionkey();
	$r = remove_account( $username );
	if( !$r ) throw new ErrorException("remove_account");
	log_info("remove_account: success.", ["username" => $username]);

	$r = store_account( $username, $totpsecret, $sessionkey );
	if( !$r ) throw new ErrorException("store_account");
	log_info("store_account: success.", ["username" => $username, "sessionkey" => $sessionkey]);

	$r = send_mail_at_account_issuance( $setting, $mail, $username, $sessionkey );
	if( !$r ) throw new ErrorException("send_mail_at_account_issuance");
	log_info("send_mail_at_account_issuance: success.", ["username" => $username, "sessionkey" => $sessionkey, "mail" => $mail]);

	$location = "signup_verify.php";

}catch(Exception $e) {
	$message = $e->getMessage();
	print "<div>捕捉した例外: {$message}</div>\n";
	log_info("exception: catch.", ["message" => $message]);
	$location = "signup.php?message={$message}";
} finally {
}

header("Location: $location");


