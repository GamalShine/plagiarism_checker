#!/bin/sh

cd /home/u704951863/public_html || exit 1

/usr/bin/php artisan queue:work database --queue=plagiarism --once --tries=1 --timeout=300 --verbose >> /home/u704951863/queue.log 2>&1
