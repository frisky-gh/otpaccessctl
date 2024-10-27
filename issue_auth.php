<?php

// load libraries
require("lib/common.php");

$location = "issue_complete.php";

try{
	$setting = load_setting();
	set_channel_of_log("issue_auth");
	log_info("load_setting: success.");

	validate_inputs();
	$username = $_POST["username"];
	$password = $_POST["password"];
	log_info("validate_inputs: success.", ["username" => $username]);

	$mail = auth_by_ldap($setting, $username, $password);
	if( !$mail ) throw new ErrorException("auth_by_ldap");
	log_info("auth_by_ldap: success.", ["username" => $username, "mail" => $mail]);

	$repo = load_repository($username, true, true);
	if( $repo ){
		// TODO: Consecutive issues shall be considered error.
	}

	$totpsecret = generate_totpsecret();
	$sessionkey = generate_sessionkey();
	$r = remove_repository( $username );
	if( !$r ) throw new ErrorException("remove_repository");
	log_info("remove_repository: success.", ["username" => $username]);

	$r = store_repository( $username, $totpsecret, $sessionkey );
	if( !$r ) throw new ErrorException("store_repository");
	log_info("store_repository: success.", ["username" => $username, "sessionkey" => $sessionkey]);

	$r = send_mail_at_issuance( $setting, $mail, $username, $sessionkey );
	if( !$r ) throw new ErrorException("send_mail_at_issuence");
	log_info("send_mail_at_issuance: success.", ["username" => $username, "sessionkey" => $sessionkey, "mail" => $mail]);

	$location = "issue_complete.php";

}catch(Exception $e) {
	$message = $e->getMessage();
	print "<div>捕捉した例外: {$message}</div>\n";
	log_info("exception: catch.", ["message" => $message]);
	$location = "issue.php?message={$message}";
} finally {
}

header("Location: $location");


