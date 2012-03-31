#!/bin/sh
out=".htmaint"
help=0
showUsage () {
    echo "== Magento Maintanence Enabler =="
    echo "Usage: $(basename $0) [ options ] [ ips ... ]"
    echo "--help|-h  Show this usage and expanded help"
    echo "--end|-e   End maintanence"
    
}
showHelp () {
    echo ""
    echo "Each operand 'ips' is added to a list of allowed IP's and maintanence is flagged"
    echo ""
    echo "If no operands are given, maintenance is flagged for all"
}

if [ "$1" = "-e" -o "$1" = "--end" ]; then
    echo "Removing maintenace lock"
    rm -f $out
    exit
elif [ "$1" = "-h" -o "$1" = "--help" ]; then
    showUsage
    showHelp
    exit
elif [ "${1:0}" = '-' ]; then
    echo "Invalid Option"
    showUsage
    exit 1
fi

if [ -n "$*" ]; then
    echo "Adding maintenance lock with passed IP exceptions"
    sep="','"
    ips=$(printf "$sep%s" "$@")
    echo "<?php return array('${ips:${#sep}}');" > $out
else
    echo "Adding maintenance lock without IP exceptions"
    printf "" > $out
fi
