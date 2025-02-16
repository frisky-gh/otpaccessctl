<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("show");
	load_messagecatalog("app", $setting["web"]["lang"]);
	log_info("load_setting: success.");

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
