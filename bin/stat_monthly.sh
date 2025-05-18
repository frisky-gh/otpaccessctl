#!/bin/bash

TOOLHOME=` ( cd "${0%/*}/.." ; pwd ) `
STATUSDIR=$TOOLHOME/status
LOGDIR=$TOOLHOME/log
BINDIR=$TOOLHOME/bin

THISMONTH=` date +%Y-%m `
LASTMONTH=` date -d "$THISMONTH-01 1 day ago" +%Y-%m `
test -f $LOGDIR/stat_monthly.$LASTMONTH && exit 0

ls $LOGDIR |
grep -P "^(stat_account|stat_pass).$LASTMONTH" |
while read f ; do
	cat $LOGDIR/$f
done |
$BINDIR/stat_monthly.pl > $LOGDIR/stat_monthly.$LASTMONTH

exit 0

