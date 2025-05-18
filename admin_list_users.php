<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("admin_list_users");
	log_info("load_setting: success.");

	validate_inputs();
	log_info("validate_inputs: success.", []);
	$unauthed = $_GET["unauthed"];

	if( $unauthed ){ $accounts = list_accounts( false ); }
	else           { $accounts = list_accounts( true ); }

	$accounts_json = json_encode( $accounts );
	log_info("list_accounts: success.", []);

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);

} finally {
}

print json_encode( $accounts );

