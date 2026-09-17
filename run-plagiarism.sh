#!/bin/sh

echo "$(date '+%Y-%m-%d %H:%M:%S') Cron started" >> /home/u704951863/queue.log

cd /home/u704951863/domains/naskahcek.com/public_html || exit 1

/usr/bin/flock -n /tmp/naskahcek-plagiarism.lock \
	/usr/bin/php artisan queue:work database --queue=plagiarism --once --tries=1 --timeout=300 --verbose \
	>> /home/u704951863/queue.log 2>&1
