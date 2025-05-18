<?php

// load libraries
require("lib/common.php");

$language_selector = null;
try{
	$setting = load_setting();
	set_channel_of_log("signin_complete");
	log_info("load_setting: success.");

	validate_inputs();
	$sessionkey = $_COOKIE["sessionkey"];
	log_info("validate_inputs: success.", ["sessionkey" => $sessionkey]);

	$lang = $_COOKIE["lang"] ?? $setting["web"]["default_lang"];
	$language_selector = generate_language_selector( $lang, $setting["web"]["lang_list"] );
	load_messagecatalog("app", $lang);
	log_info("load_messagecatalog: success.", ["lang" => $lang]);

	if     ( $sessionkey == "" ){
		// nothing to do

	}else{
		$r = pass_is_activated( $sessionkey );
		log_info("pass_is_activated: success.", ["r" => $r, "sessionkey" => $sessionkey]);

	}

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
	header("Location: resources/forbidden.html");
	exit(0);
} finally {
}

include("templates/signin_complete.tmpl");

