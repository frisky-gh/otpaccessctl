<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("activate");
	log_info("load_setting: success.");

	validate_inputs();
	$username = $_GET["username"];
	$sessionkey = $_GET["sessionkey"];
	log_info("validate_inputs: success.", ["username" => $username, "sessionkey" => $sessionkey]);

	$repo = load_repository($username, false, true);
	if( !$repo ) throw new ErrorException("load_repository");
	log_info("load_repository: success.", ["username" => $username, "repo" => $repo]);

	$repo_sessionkey = $repo["sessionkey"];
	if( $repo_sessionkey != $sessionkey ) throw new ErrorException("session_keys_are_mismatched");

	$now = time();
	$expiration_limit = $repo["creationtime"] + $setting["web"]["expiration_min_of_issue"] * 60;
	if( $now > $expiration_limit ) throw new ErrorException("issue_is_expired");

	$r = activate_repository($username);
	if( !$r ) throw new ErrorException("activate_repository");
	log_info("activate_repository: success.", ["username" => $username]);

	$url = generate_otpauth_url($username, $setting, $repo);
	log_info("generate_otpauth_url: success.", ["username" => $username, "url" => $url]);

	$svg = generate_qrcode($url);

}catch(Exception $e) {
	$message = $e->getMessage();
	print "<div>捕捉した例外: {$message}</div>\n";
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

?><html>
  <head>
  </head>
  <body>
    <?php if( isset($svg) ){ ?>
      <div>
	<?= htmlspecialchars($username) ?> さん<br>
        Google Authenticator 等で下記のQRコードを読み取ってください。
      </div>
      <?= $svg ?>
    <?php }else{ ?>
      <div>
        エラー
      </div>
    <?php } ?>
  <body>
</html>
