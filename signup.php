<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("signup");
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
  <title><?= $setting["web"]["app_name"] ?>: Issue MFA Account</title>
    <style>
      .error-message { color: red; }
    </style>
  </head>
  <body>
    <form method="POST" action="signup_auth.php">

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

      <input type="submit" name="submit" value="issue">
    </form>

    <?php if( $_GET["message"] != "" ){ ?>
        <div class="error-message">
          <?= htmlspecialchars( $_GET["message"] ) ?>
        </div>
    <?php } ?>

  </body>
</html>
