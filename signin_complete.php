<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("signin_complete");
	log_info("load_setting: success.");

	validate_inputs();
	$sessionkey = $_GET["sessionkey"];
	log_info("validate_inputs: success.", ["sessionkey" => $sessionkey]);

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

?><!DOCTYPE html>
<html>
  <head>
    <?php if( $setting["web"]["auth_method"] == "maildomain" && !isset($sessionkey) ){ ?>
        <title><?= $setting["web"]["app_name"] ?>: Successed!</title>
    <?php }elseif( $r ){ ?>
        <title><?= $setting["web"]["app_name"] ?>: Accepted!</title>
    <?php }else{ ?>
        <meta http-equiv="refresh" content="5; URL=signin_complete.php?sessionkey=<?= $sessionkey ?>" />
	<title><?= $setting["web"]["app_name"] ?>: Waiting</title>
    <?php } ?>
  </head>
  <body>
    <?php if( $setting["web"]["auth_method"] == "maildomain" ){ ?>
	Authentication has been successed!
    <?php }elseif( $r ){ ?>
	Your request has been accepted!
    <?php }else{ ?>
        Waiting...
    <?php } ?>
  </body>
</html>
