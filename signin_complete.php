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

	$r = request_is_accepted( $sessionkey );
	log_info("request_is_accepted: success.", ["r" => $r]);

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

?><!DOCTYPE html>
<html>
  <head>
    <?php if( $r ){ ?>
        <title><?= $setting["web"]["app_name"] ?>: Accepted!</title>
    <?php }else{ ?>
        <meta http-equiv="refresh" content="5; URL=signin_complete.php?sessionkey=<?= $sessionkey ?>" />
	<title><?= $setting["web"]["app_name"] ?>: Waiting</title>
    <?php } ?>
  </head>
  <body>
    <?php if( $r ){ ?>
	Your request has been accepted!
    <?php }else{ ?>
        Waiting...
    <?php } ?>
  </body>
</html>
