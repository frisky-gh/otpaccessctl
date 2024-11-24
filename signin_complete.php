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

	if     ( $setting["web"]["auth_method"] == "maildomain" && $sessionkey == "" ){
		// nothing to do

	}elseif( $setting["web"]["auth_method"] == "maildomain" ){
		$r = pass_is_activated( $sessionkey );
		log_info("pass_is_activated: success.", ["r" => $r]);

	}elseif( $setting["web"]["auth_method"] == "ldap" ){
		$r = pass_is_activated( $sessionkey );
		log_info("pass_is_activated: success.", ["r" => $r]);

	}else{
		$r = "error";
	}

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
	<?php if( $r ){ ?>
	    <title><?= $setting["web"]["app_name"] ?>: Accepted!</title>
	<?php }else{ ?>
	    <meta http-equiv="refresh" content="5; URL=signin_complete.php?sessionkey=<?= $sessionkey ?>" />
	    <title><?= $setting["web"]["app_name"] ?>: Waiting...</title>
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
			    <?php if( $r ){ ?>
				<?= $setting["web"]["app_name"] ?>: Accepted!
			    <?php }else{ ?>
				<?= $setting["web"]["app_name"] ?>: Waiting...
			    <?php } ?>
			</h1>
                        <hr class="divider" />
                    </div>


		    <!-- START -->
                    <div class="col-lg-10 align-self-baseline">
			<?php if( $r ){ ?>
			    <p class="text-white-75 mb-5">
				Your site pass has been issued!
			    </p>
			<?php }else{ ?>
			    <p class="text-white-75 mb-5">
				Waiting the site pass to access now...
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
