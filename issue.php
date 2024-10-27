<!DOCTYPE html>
<html>
  <head>
    <title>OTPAccessCtl: Issue MFA Account</title>
    <style>
      .error-message { color: red; }
    </style>
  </head>
  <body>
    <form method="POST" action="issue_auth.php">
      <div>
        username: <br>
        <input type="text" name="username" value="">
      </div>
      <div>
        password: <br>
        <input type="password" name="password" value="">
      </div>
      <input type="submit" name="submit" value="issue">
    </form>

    <?php if( $_GET["message"] != "" ){ ?>
        <div class="error-message">
          <?= htmlspecialchars( $_GET["message"] ) ?>
        </div>
    <?php } ?>

  </body>
</html>
