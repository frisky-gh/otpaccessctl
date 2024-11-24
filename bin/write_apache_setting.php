#!/usr/bin/php
<?php

require(__DIR__."/../lib/common.php");
require(__DIR__."/../lib/common_cli.php");

function store_apache_setting ( $content ) {
	file_put_contents(__DIR__."/../status/setting/apache.conf", $content);
}

try{
	$acceptedlist = load_acceptedlist();
	$apache_setting = "";
	foreach( $acceptedlist as $i ){
		if( $i == "" ) continue;
		$apache_setting .= "	Require ip $i\n";
	}
	store_apache_setting( $apache_setting );

}catch(Exception $e) {
	$message = $e->getMessage();
	echo "exception: {$message}\n";
} finally {
}

