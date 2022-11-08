#!/bin/sh -x
docker-compose exec pgsql sh -c 'pg_dump -U $POSTGRES_USER -d $POSTGRES_DB -f /app/dibby.sql'
