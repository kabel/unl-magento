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

symlinks=( \
#	"app/code/community/Zenprint" \
	"app/code/local/Unl" \
	"app/code/local/AW" \
	"app/code/local/Webshopapps" \
	"app/design/frontend/unl" \
	"app/design/adminhtml/default/unl" \
	"app/etc/modules/Unl_All.xml" \
#	"app/etc/modules/Zenprint_Xajax.xml" \
#	"app/etc/modules/Zenprint_Ordership.xml" \
	"errors/unl" \
	"errors/local.xml" \
#	"js/xajax_js" \
	"lib/SimpleCAS"
	"lib/SimpleCAS.php" \
	"lib/UNL" \
#	"lib/Xajax" \
	"skin/frontend/unl" \
	"skin/adminhtml/default/unl" \
);
#echo ${symlinks[@]}
#exit

if [ "$action" == 2 ]; then
	echo -n "Removing "
else
	echo -n "Creating "
fi
echo "symlinks in $magePath from $unlPath ..."

for i in "${symlinks[@]}"
do
	if [ "$action" == 2 ]; then
		rm "$i"
	else
		ln -s "$unlPath/$i" "$i"
	fi
done

echo "[DONE]"
exit
