#!/bin/bash

TOOLHOME=` ( cd "${0%/*}/.." ; pwd ) `
STATUSDIR=$TOOLHOME/status
LOGDIR=$TOOLHOME/log
BINDIR=$TOOLHOME/bin


for i in `seq 1 7` ; do
	DAY=` date -d "$i days ago" +%Y-%m-%d `

	if [ ! -f $LOGDIR/stat_pass.$DAY ] ; then
		find $STATUSDIR -type f -name '*.ini' |
		xargs -r stat -c '%y %n' |
		awk -v d=$DAY '$1==d{ print $4; }' |
		$BINDIR/stat_pass.pl > $LOGDIR/stat_pass.$DAY
	fi
	if [ ! -f $LOGDIR/stat_account.$DAY ] ; then
		(
			grep "signup_auth INFO send_mail_at_account_issuance: success." $LOGDIR/app_$DAY.log
			grep "signup_auth2nd INFO generate_otpauth_url: success." $LOGDIR/app_$DAY.log
	       	) |
		$BINDIR/stat_account.pl > $LOGDIR/stat_account.$DAY
	fi
done


exit 0

