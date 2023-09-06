#!/bin/sh -x

DOMAIN="dibby.matthewturland.com"
DIBBY_ROOT="/home/pi/Documents/Code/dibby"
DIBBY_WEBROOT="$DIBBY_ROOT/public"
SSL_SRC_PATH="/etc/letsencrypt/live/$DOMAIN"
SSL_DST_PATH="$DIBBY_ROOT/docker/nginx/letsencrypt/archive/$DOMAIN"

sudo certbot certonly --force-renew -n --webroot --webroot-path $DIBBY_WEBROOT -d $DOMAIN

sudo cp $SSL_SRC_PATH/fullchain.pem $SSL_DST_PATH/fullchain4.pem
sudo cp $SSL_SRC_PATH/privkey.pem $SSL_DST_PATH/privkey4.pem

cd $DIBBY_ROOT
docker-compose down
docker-compose up -d
