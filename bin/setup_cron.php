#!/usr/bin/php
<?php

require(__DIR__."/../lib/common.php");
require(__DIR__."/../lib/common_cli.php");

try{
	$setting = load_setting();

	$wd = get_workingdir();
	$period = $setting["cron"]["period_min_of_execution"];
	$job = "cd {$wd} ; bin/cron.php";

	//$ansible_cron_arg = "name='otpaccessctl cron job' minute='*/{$period}' job='{$job}'";
	//$ansible_cmd = "ansible -i loclahost, -c local -m cron -a \"{$ansible_cron_arg}\" all";
	//$r = system( $ansible_cmd );

	$crontab = [];
	$crontab_job = "*/{$period} * * * * {$job}\n";
	$label = "# otpaccessctl job\n";
	$label_index = null;
	$in = popen( "crontab -l", "r" );
	while( ($i = fgets($in)) !== false ){
		if( $i == $label ) $label_index = count($crontab);
		array_push( $crontab, $i );
	}
	fclose( $in );
	if( is_null($label_index) ) array_push( $crontab, $label, $crontab_job );
	else $crontab[$label_index + 1] = $crontab_job;

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

