<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("admin_modify_comment");
	log_info("load_setting: success.");

	validate_inputs();
	$username = $_POST["username"];
	$comment  = $_POST["comment"];
	$ipaddr     = $_SERVER['REMOTE_ADDR'] ?? "-";
	$remoteuser = $_SERVER['REMOTE_USER'] ?? "-";
	log_info("validate_inputs: success.", ["username" => $username, "comment" => $comment]);

	$r = set_parameter_of_account( $username, "comment", $comment );
	log_info("set_parameter_of_account: success.", ["username" => $username, "comment" => $comment, "r" => $r, "remoteuser" => $remoteuser, "ipaddr" => $ipaddr]);

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);

} finally {
}

print json_encode( ["result" => $r] );

