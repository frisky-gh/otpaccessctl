<?php

// load libraries
require("lib/common.php");

$language_selector = null;
try{
	$setting = load_setting();
	set_channel_of_log("show");
	log_info("load_setting: success.");

	validate_inputs();
	log_info("validate_inputs: success.", []);

	$lang = $_COOKIE["lang"] ?? $setting["web"]["default_lang"];
	$language_selector = generate_language_selector( $lang, $setting["web"]["lang_list"] );
	load_messagecatalog("app", $lang);
	log_info("load_messagecatalog: success.", ["lang" => $lang]);

	$acceptedlist = load_acceptedlist();
	log_info("load_acceptedlist: success.", []);

	$ipaddr = $_SERVER['REMOTE_ADDR'];
	if     ( in_array($ipaddr, $acceptedlist) ){
		log_info("in_array: success.", ["ipaddr" => $ipaddr]);
		$hit = true;

	}else{
		log_info("in_array: failed.", ["ipaddr" => $ipaddr]);
		$hit = false;
	}

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

include("templates/show.tmpl");
