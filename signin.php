<?php

// load libraries
require("lib/common.php");

$language_selector = null;
try{
	$setting = load_setting();
	set_channel_of_log("signin");
	log_info("load_setting: success.");

	validate_inputs();
	$message = $_GET["message"];
	$sessionkey4signout = $_COOKIE["sessionkey4signout"];
	log_info("validate_inputs: success.", ["message" => $message, "sessionkey4signout" => $sessionkey4signout]);

	$lang = $_COOKIE["lang"] ?? $setting["web"]["default_lang"];
	$language_selector = generate_language_selector( $lang, $setting["web"]["lang_list"] );
	load_messagecatalog("app", $lang);
	log_info("load_messagecatalog: success.", ["lang" => $lang]);

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

include("templates/signin.tmpl");

