#!/usr/bin/env bash
find . -type d -exec chmod 770 {} \;
find . -type f -exec chmod 660 {} \;
