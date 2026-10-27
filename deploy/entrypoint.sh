#!/bin/sh
set -eu
mkdir -p /app/storage
chown -R www-data:www-data /app/storage
chmod 2775 /app/storage
umask 0002
exec "$@"
