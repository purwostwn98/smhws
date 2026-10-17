#!/bin/sh
set -e

chmod -R ug+rwX /var/www/html/writable 2>/dev/null || true

exec "$@"
