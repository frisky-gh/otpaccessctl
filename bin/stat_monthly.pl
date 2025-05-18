#!/usr/bin/perl

use strict;

my %stat = ();

while( <STDIN> ){
	chomp;
	die "$_, stopped" unless m"^(.*)=(.*)$";
	$stat{$1} += $2;
}

foreach my $k ( sort keys %stat ){
	my $v = $stat{$k};
	print "$k=$v\n";
}

