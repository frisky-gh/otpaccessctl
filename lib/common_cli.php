<?php

function handle_error_and_throw_it_as_exception_in_cli($severity, $message, $filename, $lineno) {
	log_tty("exception: {$severity}: {$message} in {$filename}:{$lineno}");
	throw new ErrorException($message, 0, $severity, $filename, $lineno);
}
set_error_handler('handle_error_and_throw_it_as_exception_in_cli');
ini_set('display_errors', 1);

//// functions about logging
$tty = posix_ttyname( 1 );
$procname = preg_replace('|^.*/|', "", $argv[0]);

function log_tty( $message ) {
	global $tty;
	global $procname;
	if( $tty == false ) return;
	echo "{$procname}: {$message}\n";
}

//// functions about working dir
$wd = null;
function get_workingdir () {
	global $wd;
	if( !is_null($wd) ) return $wd;
	$wd_orig = __DIR__ . "/..";
	exec( "cd {$wd_orig} ; pwd", $out, $result );
	$wd = $out[0];
	return $wd;
}

//// functions about locking
function lock_process( &$lock ) {
	$lock = fopen( get_workingdir()."/status/lock", "w" );
	if( flock($lock, LOCK_EX | LOCK_NB) ) return true;

	fclose($lock);
	return false;
}

function unlock_process( &$lock ) {
	flock($lock, LOCK_UN);
        fclose($lock);
}


//// functions about standby
function standby_during_period_of_exec ( $setting, $starttime, $acceptance_func ){
	$interval_sec = $setting["cron"]["interval_sec_of_acceptance"];
	$standby_endtime = $starttime + $setting["cron"]["interval_min_of_exec_by_cron"] * 60 - $interval_sec;

	log_tty("standby started.");
	while( time() < $standby_endtime ){
		sleep($interval_sec);
		$acceptance_func();
	}
	log_tty("standby ended.");
}

function activate_and_cleanup_passes ( $setting, $must_be_cleaned_up = true ) {
	$acceptedlist_is_changed = false;
	activate_passes( $acceptedlist_is_changed );
	log_tty("activated the new passes.");
	if( $must_be_cleaned_up || cleanup_is_directed() ){
	       	cleanup_passes( $setting, $acceptedlist_is_changed );
		log_tty("cleanup the old requests.");
	}
	if( !$must_be_cleaned_up && !$acceptedlist_is_changed ) return;

	$acceptedlist      = generate_acceptedlist();
	$last_acceptedlist = load_acceptedlist();
	if( !two_acceptedlists_are_diffent($acceptedlist, $last_acceptedlist) ) return;

	log_tty("the accepted list should be updated.");
	log_info("the accepted list should be updated.");
	store_acceptedlist( $acceptedlist );

	$write_command  = $setting["cron"]["write_command"];
	$reload_command = $setting["cron"]["reload_command"];
	if( $write_command != "" ){
		log_tty( "write_command: {$write_command}" );
		$output = system( "{$write_command}", $result );
		log_tty("write_command: output={$output}, result={$result}");
		log_info("write_command: output={$output}, result={$result}");
		if( $result != 0 ) return ;
	}
	if( $reload_command != "" ){
		log_tty( "reload_command: {$reload_command}" );
		$output = system( "{$reload_command}", $result );
		log_tty("reload_command: output={$output}, result={$result}");
		log_info("reload_command: output={$output}, result={$result}");
		if( $result != 0 ) return ;
	}

	log_info("done.");
	log_tty("done.");
}

