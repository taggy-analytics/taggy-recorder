#!/bin/bash

echo "Hello, world"
grep -q '^DIESISTEINTEST=' ./.env || echo 'DIESISTEINTEST=1234!' >> ./.env

if [ ! -f /etc/supervisor/conf.d/watchtest.conf ]; then
    # File doesn't exist, create and add configuration
    echo "[program:watchtest]
process_name=%(program_name)s
directory=/var/www/taggy/current
command=php /var/www/taggy/current/artisan taggy:watch-recording-segmentsa
autostart=true
autorestart=true
user=taggy
redirect_stderr=true
stdout_logfile=/var/www/taggy/current/storage/logs/watch-recording-segments.log
stopwaitsecs=3600" > /etc/supervisor/conf.d/watch.conf
fi

sudo supervisorctl reread
sudo supervisorctl update
