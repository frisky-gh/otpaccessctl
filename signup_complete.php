<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("signup_complete");
	log_info("load_setting: success.");

	validate_inputs();
	$message = $_GET["message"];
	log_info("validate_inputs: success.", ["message" => $message]);

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

?><!DOCTYPE html>
<html>
  <head>
    <title><?= $setting["web"]["app_name"] ?>: Complete Issuance</title>
  </head>
  <body>
    Your MFA account has been issued.<br>
    Please receive the issuance mail, and follow the instructions in the mail to import your account into your authenticator app.
  </body>
</html>
