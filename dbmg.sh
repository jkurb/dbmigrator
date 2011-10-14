#!/bin/sh

if [ $# -eq 0 ];
then
    php -f dbmigrator.php help
else
    php -f dbmigrator.php $@
fi