#!/usr/bin/php
<?php

require(__DIR__."/../lib/common.php");
require(__DIR__."/../lib/common_cli.php");

try{
	$setting = load_setting();

	$wd = get_workingdir();
	$period = $setting["cron"]["interval_min_of_exec_by_cron"];

	//$ansible_cron_arg = "name='otpaccessctl cron job' minute='*/{$period}' job='{$job}'";
	//$ansible_cmd = "ansible -i loclahost, -c local -m cron -a \"{$ansible_cron_arg}\" all";
	//$r = system( $ansible_cmd );

	$crontab = [];

	$job = "cd {$wd} ; bin/cron.php";
	$job_crontab = "*/{$period} * * * * {$job}\n";
	$job_label = "# otpaccessctl job\n";
	$job_label_index = null;

	$job2 = "cd {$wd} ; bin/cleanup.sh ; bin/stat_daily.sh";
	$job2_crontab = "09 00 * * * {$job2}\n";
	$job2_label = "# otpaccessctl cleanup\n";
	$job2_label_index = null;

	$in = popen( "crontab -l", "r" );
	while( ($i = fgets($in)) !== false ){
		if( $i == $job_label )  $job_label_index  = count($crontab);
		if( $i == $job2_label ) $job2_label_index = count($crontab);
		array_push( $crontab, $i );
	}
	fclose( $in );
	if( is_null($job_label_index) )  array_push( $crontab, $job_label,  $job_crontab );
	else $crontab[$job_label_index  + 1] = $job_crontab;
	if( is_null($job2_label_index) ) array_push( $crontab, $job2_label, $job2_crontab );
	else $crontab[$job2_label_index + 1] = $job2_crontab;

	$out = popen( "crontab", "w" );
	foreach( $crontab as $i ){
		fputs( $out, $i );
	}
	fclose( $out );

	exit( 0 );

}catch(Exception $e) {
	$message = $e->getMessage();
	echo "exception: {$message}\n";
} finally {
}

