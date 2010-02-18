#!/bin/sh
if [ -f .htaccess.maint ]
then
	mv .htaccess .htaccess.live
	mv .htaccess.maint .htaccess
else
	mv .htaccess .htaccess.maint
	mv .htaccess.live .htaccess
fi
