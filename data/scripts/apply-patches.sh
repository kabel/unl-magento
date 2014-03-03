#!/usr/bin/env bash
for i in $MODULE/data/patches/*.patch;
do
	if [ ! -f $i.applied ]; then
		patch -d $PROJECT -p0 < $i
		touch $i.applied
	fi
done
