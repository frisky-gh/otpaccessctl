#!/usr/bin/php
<?php

require(__DIR__."/../lib/common.php");
require(__DIR__."/../lib/common_cli.php");

$starttime = time();

try{
	chdir( get_workingdir() );

	$setting = load_setting();
	set_channel_of_log("cron");
	log_info("load_setting: success.");

	// lock
	$lock = null;
	if( !lock_process($lock) ){
		log_tty("process is duplicated.");
		direct_cleanup();
		exit(2);
	}

	activate_and_cleanup_passes($setting, true);
	standby_during_period_of_exec (
		$setting,
		$starttime,
		function() use ($setting) { activate_and_cleanup_passes($setting, false); }
	);
	unlock_process($lock);
	log_info("activate_and_cleanup_passes: success.");

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

