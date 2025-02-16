#!/bin/bash

TOOLHOME=` ( cd "${0%/*}/.." ; pwd ) `
STATUSDIR=$TOOLHOME/status
LOGDIR=$TOOLHOME/log

TODAY=` date +%Y-%m-%d `
for i in account_unauthed pass_expired pass_inactive pass_unauthed ; do
	find $STATUSDIR/$i -maxdepth 1 -type f -name '*.ini' -mtime +30 |
	xargs -r stat -c "%y %n" |
	while read YMD TIME TZ FILEPATH ; do
		YM=${YMD%-*}

		d=$LOGDIR/$YM.$i
		test -d $d || mkdir $d
		mv $FILEPATH $d
	done
done


exit 0

