#!/usr/bin/perl

use strict;

sub read_ini ($) {
	my ($filepath) = @_;
	my %ini;
	open my $h, "<", $filepath or die "$filepath: cannot open, stopped";
	while( <$h> ){
		chomp;
		die unless m"^(\w+)=(.*)$";
		$ini{$1} = $2;
	}
	close $h;
	return %ini;
}


my %stat = ();

while( <STDIN> ){
	chomp;
	die "$_, stopped" unless m"^(.*/(pass_unauthed|pass_inactive|pass_expired|pass|account_unauthed|account)/(.*\.ini))";
	
	my $filepath = $1;
	my $type  = $2;

	my %ini = read_ini $filepath;
	my $username = $ini{username};

	if( $type eq "pass_unauthed"    ){ $stat{unauthed_pass}++;    $stat{"unauthed_pass:$username"}++; }
	if( $type eq "pass_inactive"    ){ $stat{inactive_pass}++;    $stat{"inactive_pass:$username"}++; }
	if( $type eq "pass_expired"     ){ $stat{active_pass}++;      $stat{"active_pass:$username"}++; }
	if( $type eq "pass"             ){ $stat{active_pass}++;      $stat{"active_pass:$username"}++; }
	#if( $type eq "account_unauthed" ){ $stat{unauthed_account}++; $stat{"unauthed_account:$username"}++; }
	#if( $type eq "account"          ){ $stat{active_account}++;   $stat{"active_account:$username"}++; }
}

foreach my $k ( sort keys %stat ){
	my $v = $stat{$k};
	print "$k=$v\n";
}

