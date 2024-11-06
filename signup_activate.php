<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("signup_activate");
	log_info("load_setting: success.");

	if( $_SERVER['REQUEST_METHOD']  == "HEAD" ) throw new ErrorException("access_from_bot");
	if( $_SERVER['HTTP_USER_AGENT'] == "" )     throw new ErrorException("access_from_bot");

	validate_inputs();
	$username = $_GET["username"];
	$sessionkey = $_GET["sessionkey"];
	log_info("validate_inputs: success.", ["username" => $username, "sessionkey" => $sessionkey]);

	$acct = load_account($username, false, true);
	if( !$acct ) throw new ErrorException("load_account");
	log_info("load_account: success.", ["username" => $username, "acct" => $acct]);

	$acct_sessionkey = $acct["sessionkey"];
	if( $acct_sessionkey != $sessionkey ) throw new ErrorException("session_keys_are_mismatched");

	$now = time();
	$expiration_limit = $acct["creationtime"] + $setting["web"]["expiration_min_of_issue"] * 60;
	if( $now > $expiration_limit ) throw new ErrorException("issue_is_expired");

	$r = activate_account($username);
	if( !$r ) throw new ErrorException("activate_account");
	log_info("activate_acccount: success.", ["username" => $username]);

	$url = generate_otpauth_url($username, $setting, $acct);
	log_info("generate_otpauth_url: success.", ["username" => $username, "url" => $url]);

	$svg = generate_qrcode($url);

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

?><html>
  <head>
    <?php if( isset($svg) ){ ?>
      <title><?= $setting["web"]["app_name"] ?>: Your MFA Account has been activated</title>
    <?php }else{ ?>
      <title><?= $setting["web"]["app_name"] ?>: Error</title>
    <?php } ?>
  </head>
  <body>
    <?php if( isset($svg) ){ ?>
      <div>
	Hi <?= htmlspecialchars($username) ?>-san,<br>
        Please scan the QR code bellow with Google Authenticator or similar app.
      </div>
      <?= $svg ?>
    <?php }else{ ?>
      <div>
        Error.
      </div>
    <?php } ?>
  <body>
</html>
