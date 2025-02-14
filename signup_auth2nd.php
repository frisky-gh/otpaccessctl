<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("signup_auth2nd");
	log_info("load_setting: success.");

	if( $_SERVER['REQUEST_METHOD']  == "HEAD" ) throw new ErrorException("access_from_bot");
	if( $_SERVER['HTTP_USER_AGENT'] == "" )     throw new ErrorException("access_from_bot");

	validate_inputs();
	$username = $_GET["username"];
	$sessionkey = $_GET["sessionkey"];
	log_info("validate_inputs: success.", ["username" => $username, "sessionkey" => $sessionkey]);

	$acct = load_account($username, false, true);
	if( !$acct ) throw new ErrorException("error_in_load_account");
	log_info("load_account: success.", ["username" => $username, "acct" => $acct]);

	$acct_sessionkey = $acct["sessionkey"];
	if( $acct_sessionkey != $sessionkey ) throw new ErrorException("unmatch_session_key");

	$now = time();
	$expiration_limit = $acct["creationtime"] + $setting["web"]["expiration_min_of_issuance"] * 60;
	if( $now > $expiration_limit ) throw new ErrorException("expired_issuance");

	$r = validate_account($username);
	if( !$r ) throw new ErrorException("error_in_validate_account");
	log_info("validate_acccount: success.", ["username" => $username]);

	$url = generate_otpauth_url($username, $setting, $acct);
	log_info("generate_otpauth_url: success.", ["username" => $username, "url" => $url]);

	$svg = generate_qrcode($url);

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />


        <!-- <title>Creative - Start Bootstrap Theme</title> -->
	<?php if( isset($svg) ){ ?>
	    <title><?= $setting["web"]["app_name"] ?>: Issued Your Account</title>
	<?php }else{ ?>
	    <title><?= $setting["web"]["app_name"] ?>: Error</title>
	<?php } ?>


        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Bootstrap Icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Google fonts-->
        <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic" rel="stylesheet" type="text/css" />
        <!-- SimpleLightbox plugin CSS-->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/SimpleLightbox/2.1.0/simpleLightbox.min.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="resources/css/styles.css" rel="stylesheet" />
    </head>
    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3" id="mainNav">
            <div class="container px-4 px-lg-5">
	        <a class="navbar-brand" href="#page-top"><?= $setting["web"]["org_name"] ?></a>
                <button class="navbar-toggler navbar-toggler-right" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ms-auto my-2 my-lg-0">
                        <li class="nav-item"><a class="nav-link" href="signin.php">Sign in</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Masthead-->
        <header class="masthead">
            <div class="container px-4 px-lg-5 h-50">
                <div class="row gx-4 gx-lg-5 h-50 align-items-center justify-content-center text-center">
                    <div class="col-lg-12 align-self-end">
			<h1 id="signin" class="text-white font-weight-bold">
			  <?= $setting["web"]["app_name"] ?>: Issued Your Account
			</h1>
                        <hr class="divider" />
                    </div>


		    <!-- START -->
                    <div class="col-lg-10 align-self-baseline">
			<?php if( isset($svg) ){ ?>
			    <p class="text-white-75 mb-5">
				Hi <?= htmlspecialchars($username) ?>-san,<br>
				Please scan the QR code bellow with Google Authenticator or similar app.
			    </p>
			    <?= $svg ?>
			<?php }else{ ?>
			    <p class="text-white-75 mb-5">
				Error.
			    </p>
			<?php } ?>
                    </div>
		    <!-- END -->

                </div>
            </div>
        </header>

        <!-- Footer-->
        <footer class="bg-light py-5">
	    <div class="container px-4 px-lg-5"><div class="small text-center text-muted">Copyright &copy; 2024 - <?= $setting["web"]["org_name"] ?></div></div>
        </footer>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- SimpleLightbox plugin JS-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/SimpleLightbox/2.1.0/simpleLightbox.min.js"></script>
        <!-- Core theme JS-->
        <script src="resources/js/scripts.js"></script>
        <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
        <!-- * *                               SB Forms JS                               * *-->
        <!-- * * Activate your form at https://startbootstrap.com/solution/contact-forms * *-->
        <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
        <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
    </body>
</html>
