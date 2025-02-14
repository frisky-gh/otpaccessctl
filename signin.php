<?php

// load libraries
require("lib/common.php");

try{
	$setting = load_setting();
	set_channel_of_log("signin");
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
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />


        <!-- <title>Creative - Start Bootstrap Theme</title> -->
        <title><?= $setting["web"]["app_name"] ?>: Sign in</title>


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
                        <li class="nav-item"><a class="nav-link" href="show.php">Show status</a></li>
                        <li class="nav-item"><a class="nav-link" href="#signin">Sign in</a></li>
                        <li class="nav-item"><a class="nav-link" href="#signup">Sign up</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Masthead-->
        <header class="masthead">
            <div class="container px-4 px-lg-5 h-100">
                <div class="row gx-4 gx-lg-5 h-100 align-items-center justify-content-center text-center">
                    <div class="col-lg-8 align-self-end">
			<h1 id="signin" class="text-white font-weight-bold">
			  <?= $setting["web"]["app_name"] ?>: Sign in
			</h1>
                        <hr class="divider" />
                    </div>

		    <!-- START -->
        	    <form method="POST" action="signin_auth.php">
	              <div class="form-floating mb-3">
	                <input class="form-control" name="username" id="username" type="text" placeholder="Enter your name..." data-sb-validations="required" />
	                <label for="name">User name</label>
	                <div class="invalid-feedback" data-sb-feedback="name:required">A name is required.</div>
	              </div>

	              <?php if( $setting["web"]["auth_method"] == "ldap" ){ ?>
	                <div class="form-floating mb-3">
	                  <input class="form-control" name="password" id="password" type="password" placeholder="********" data-sb-validations="required" />
	                  <label for="name">Password</label>
	                </div>
	              <?php } ?>

	              <div class="form-floating mb-3">
	                <input class="form-control" name="token" id="token" type="text" placeholder="123456" data-sb-validations="required" />
	                <label for="name">OTP token</label>
	              </div>

	              <input type="submit" id="submit" value="Issue the site pass (sign in)" class="btn btn-primary btn-xl" >

                      <div class="col-lg-10 align-self-baseline dialog" id="dialog-empty_username">
                        Username is empty.
	              </div>
                      <div class="col-lg-10 align-self-baseline dialog" id="dialog-empty_token">
                        OTP token is empty.
	              </div>
                      <div class="col-lg-10 align-self-baseline dialog" id="dialog-unmatch_username_or_token">
                        Username or OTP token is incorrect.
	              </div>
                      <div class="col-lg-10 align-self-baseline dialog" id="dialog-other">
		      Error <?= $message ?> occured.
	              </div>

	            </form>
		    <!-- END -->

                </div>
            </div>
        </header>

        <!-- Services-->
        <section class="page-section" id="signup">
            <div class="container px-4 px-lg-5">
                <div class="row gx-4 gx-lg-5 h-100 align-items-center justify-content-center text-center">
                    <div class="col-lg-8 align-self-baseline">
                          <a class="btn btn-primary btn-xl" href="signup.php">Issue Your Account (Sign up)</a>
                    </div>
                </div>
            </div>
        </section>

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
