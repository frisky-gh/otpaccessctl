<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("signup_confirm");
	log_info("load_setting: success.");

	if( $_SERVER['REQUEST_METHOD']  == "HEAD" ) throw new ErrorException("access_from_bot");
	if( $_SERVER['HTTP_USER_AGENT'] == "" )     throw new ErrorException("access_from_bot");

	validate_inputs();
	$username = $_GET["username"];
	$sessionkey = $_GET["sessionkey"];
	log_info("validate_inputs: success.", ["username" => $username, "sessionkey" => $sessionkey]);

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

?><html>
  <head>
    <title><?= $setting["web"]["app_name"] ?>: Confirm</title>
  </head>
  <body>

    <form method="GET" action="signup_auth2nd.php">
      <input type="hidden" name="username" value="<?=$username?>">
      <input type="hidden" name="sessionkey" value="<?=$sessionkey?>">
      <input type="submit" name="submit" value="confirm">
    </form">

  <body>
</html>
