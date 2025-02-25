<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("signin_complete");
	load_messagecatalog("app", $setting["web"]["lang"]);
	log_info("load_setting: success.");

	validate_inputs();
	$sessionkey = $_GET["sessionkey"];
	log_info("validate_inputs: success.", ["sessionkey" => $sessionkey]);

	if     ( $setting["web"]["auth_method"] == "maildomain" && $sessionkey == "" ){
		// nothing to do

	}elseif( $setting["web"]["auth_method"] == "maildomain" ){
		$r = pass_is_activated( $sessionkey );
		log_info("pass_is_activated: success.", ["r" => $r, "sessionkey" => $sessionkey]);

	}elseif( $setting["web"]["auth_method"] == "ldap" ){
		$r = pass_is_activated( $sessionkey );
		log_info("pass_is_activated: success.", ["r" => $r]);

	}else{
		$r = "error";
	}

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
	header("Location: resources/forbidden.html");
	exit(0);
} finally {
}

include("templates/signin_complete.tmpl");

