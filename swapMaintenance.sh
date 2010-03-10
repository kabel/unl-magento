#!/bin/sh
if [ -f .htmaint ]
then
	rm .htmaint
else
	touch .htmaint
fi
