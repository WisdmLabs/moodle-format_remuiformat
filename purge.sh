#!/usr/bin/env sh
PHP=`which php`
php /var/www/html/moodle34/admin/cli/purge_caches.php
php /var/www/html/moodle35/admin/cli/purge_caches.php
php /var/www/html/moodle36/admin/cli/purge_caches.php
