#!/usr/bin/php
<?php

require(__DIR__."/../lib/common.php");
require(__DIR__."/../lib/common_cli.php");

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

	maintain_passes($setting, true);
	standby_during_period_of_exec (
		$setting,
		function() use ($setting) { maintenance_requests($setting, false); }
	);
	unlock_process($lock);
	log_info("maintenance_requests: success.");

}catch(Exception $e) {
	$message = $e->getMessage();
	log_info("exception: catch.", ["message" => $message]);
} finally {
}

