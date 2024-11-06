<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("req");
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
    <title>OTPAccessCtl: Sign in</title>
  </head>
  <body>
    <form method="POST" action="req_auth.php">
      <div>
        username: <br>
        <input type="text" name="username" value="">
      </div>

      <?php if( $setting["web"]["auth_method"] == "ldap" ){ ?>
        <div>
          password: <br>
          <input type="password" name="password" value="">
        </div>
      <?php } ?>

      <div>
        token: <br>
        <input type="text" name="token" value="">
      </div>
      <input type="submit" name="submit" value="use">
    </form>

    <a href="issue.php">issue QR code for authenticator</a><br>

<?php
	print "ok\n";
?>
  </body>
</html>
