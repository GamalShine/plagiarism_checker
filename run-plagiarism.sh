#!/bin/sh

START_WORKER="${1:-1}"
END_WORKER="${2:-1}"
APP_DIR="/home/u704951863/domains/naskahcek.com/public_html"
LOG_DIR="$APP_DIR/storage/logs"

cd "$APP_DIR" || exit 1

mkdir -p "$LOG_DIR"

for WORKER_ID in $(seq "$START_WORKER" "$END_WORKER"); do
	LOCK_FILE="/tmp/naskahcek-plagiarism-${WORKER_ID}.lock"
	LOG_FILE="${LOG_DIR}/queue-${WORKER_ID}.log"

	{
		echo "$(date '+%Y-%m-%d %H:%M:%S') Worker ${WORKER_ID} started"
		/usr/bin/flock -n "$LOCK_FILE" \
				/usr/bin/php artisan queue:work database --queue=plagiarism --once --tries=1 --timeout=300 --verbose
		echo "$(date '+%Y-%m-%d %H:%M:%S') Worker ${WORKER_ID} finished"
	} >> "$LOG_FILE" 2>&1 &
done

wait
