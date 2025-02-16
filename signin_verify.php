<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("signin_verify");
	load_messagecatalog("app", $setting["web"]["lang"]);
	log_info("load_setting: success.");

	validate_inputs();
	$sessionkey = $_GET["sessionkey"];
	$ipaddr = $_GET["ipaddr"];
	log_info("validate_inputs: success.", ["sessionkey" => $sessionkey, "ipaddr" => $ipaddr]);

	if     ( $setting["web"]["auth_method"] == "maildomain" && $sessionkey == "" ){
		// nothing to do

	}elseif( $setting["web"]["auth_method"] == "maildomain" ){
		$r = pass_is_activated( $sessionkey );
		log_info("pass_is_activated: success.", ["r" => $r]);

	}elseif( $setting["web"]["auth_method"] == "ldap" ){
		$r = pass_is_activated( $sessionkey );
		log_info("pass_is_activated: success.", ["r" => $r]);

	}else{
	}

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

include("templates/signin_verify.tmpl");

