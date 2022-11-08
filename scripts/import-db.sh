#!/bin/sh -x
docker-compose exec pgsql sh -c 'psql -U $POSTGRES_USER -d postgres -c "DROP DATABASE $POSTGRES_DB;" -c "CREATE DATABASE $POSTGRES_DB;" ; psql -U $POSTGRES_USER -d $POSTGRES_DB -f /app/dibby.sql'

