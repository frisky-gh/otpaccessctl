#!/usr/bin/php
<?php

require(__DIR__."/../lib/common_cli.php");

try{
	$r = system("sudo systemctl reload apache2");

}catch(Exception $e) {
	$message = $e->getMessage();
	echo "exception: {$message}\n";
} finally {
}

