#!/usr/bin/perl

use strict;
use JSON;

my $codec = JSON->new;
my %stat = ();

while( <STDIN> ){
	chomp;
	die "$_, stopped" unless m"^.* (signup_auth|signup_auth2nd) .* (\{.*\})$";
	my $type = $1;
	my $json = $2;
	my $obj = $codec->decode( $json );
	my $username = $obj->{username};

	if( $type eq "signup_auth"    ){ $stat{"account"}++;           $stat{"account:$username"}++; }
	if( $type eq "signup_auth2nd" ){ $stat{"activated_account"}++; $stat{"activated_account:$username"}++; }
}

foreach my $k ( sort keys %stat ){
	my $v = $stat{$k};
	print "$k=$v\n";
}

