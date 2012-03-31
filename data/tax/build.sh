#!/bin/sh

out="rates.csv"
rm -f $out
for i in `ls results/*.csv`
do
	sed 1d $i >> $out
done
