<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("signin");
	load_messagecatalog("app", $setting["web"]["lang"]);
	log_info("load_setting: success.");

	validate_inputs();
	$message = $_GET["message"];
	log_info("validate_inputs: success.", ["message" => $message]);

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

include("templates/signin.tmpl");

