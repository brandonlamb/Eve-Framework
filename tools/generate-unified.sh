#!/bin/sh
UNIFIED="../src/Eve/unified.php"

echo "<?php\n" > $UNIFIED

xargs --arg-file filelist.txt -I % sh -c "/usr/bin/php -w % | grep -v \"<?php\" >> $UNIFIED"


#for fn in `cat merge-files.txt`; do
#	/usr/bin/php -w $fn | grep -v "<?php" >> output/merged.php
#done
