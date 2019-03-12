#!/bin/sh

set -e

envsubst < /var/www/html/config.txt > /var/www/html/.config.ini
chmod 640 /var/www/html/.config.ini
chown -R nginx: /var/www/html/
chown nobody:root /var/www/html/.config.ini

php-fpm7 && nginx -g 'daemon off;'
