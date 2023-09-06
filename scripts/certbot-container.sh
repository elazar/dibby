#!/bin/sh

# This script is kept for posterity and should not be used.
#
# Usage:
#
# 1. Comment out this line in docker-compose.yml.
#
# - ./docker/nginx/ssl.conf:/etc/nginx/conf.d/ssl.conf
#
# 2. Bring services up.
#
# docker-compose up -d
#
# 3. Register the SSL certificate.
#
# docker-compose exec nginx ./scripts/certbot.sh
#
# 4. Bring services down.
#
# docker-compose down
#
# 5. Uncomment the line from step 1.
#
# 6. Bring services up.
#
# docker-compose up -d

apk add certbot

certbot certonly \
    --non-interactive \
    --agree-tos \
    --no-eff-email \
    --webroot \
    --webroot-path /app/public \
    --config-dir /app/docker/nginx/letsencrypt \
    --logs-dir /app/docker/nginx/letsencrypt \
    -d dibby.matthewturland.com \
    -m tobias382@gmail.com

rm -fR /app/public/.well-known
