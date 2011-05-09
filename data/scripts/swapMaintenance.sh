#!/bin/sh
if [ -f .htmaint ]
then
    echo "Removing maintenace lock..."
	rm .htmaint
else
    echo "Adding maintenace lock..."
	touch .htmaint
fi
