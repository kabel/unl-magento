#!/usr/bin/env bash
find . -type f -exec chmod 440 {} \;
find . -type d -exec chmod 550 {} \; 
find var/ -type f -exec chmod 660 {} \; 
find media/ -type f -exec chmod 660 {} \;
find var/ -type d -exec chmod 770 {} \; 
find media/ -type d -exec chmod 770 {} \;

chmod +x cron.sh
chmod +x mage
