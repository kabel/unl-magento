#!/bin/sh
if [ ! -d "$1" -o ! -d "$2" ]; then
    echo "Usage: $0 source target"
    exit 1
fi

magePath=`cd $2; pwd`;
unlPath=`cd $1; pwd`;

action=1
if [ "$3" == "revert" ]; then
    action=2
fi

cd $magePath

symlinks=(
    "app/code/local/Cm"
    "app/code/local/Mage"
    "app/code/local/Unl"
    "app/code/local/Zend"
    "app/design/frontend/unl"
    "app/design/adminhtml/default/unl"
    "app/etc/modules/Unl_All.xml"
    "errors/unl"
    "errors/local.xml"
    "js/unl"
    "lib/LinLibertineFont513"
    "lib/SimpleCAS"
    "lib/SimpleCAS.php"
    "lib/UNL"
    "skin/frontend/unl"
    "skin/adminhtml/default/unl"
);

#echo ${symlinks[@]}
#exit

if [ "$action" == 2 ]; then
    /bin/echo -n "Removing "
else
    /bin/echo -n "Creating "
fi
echo "symlinks in $magePath from $unlPath ... (press ^C to stop, 5 seconds)"
sleep 5

for i in "${symlinks[@]}"
do
    if [ "$action" == 2 ]; then
        rm -f "$i"
    elif [ ! -L "$i" ]; then
        ln -s "$unlPath/$i" "$i"
    fi
done

echo "[DONE]"
exit
