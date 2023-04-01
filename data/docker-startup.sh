#!/bin/bash

composer install
#npm install
#vendor/bin/phinx migrate

chmod a+rw /var/www/sqlite/hotdesk.db
chown -R 33:33 /var/www/sqlite/

#apache2 -DFOREGROUND
apachectl -D FOREGROUND

#access to app log
chmod a+rw /var/www/html/log/app.log

