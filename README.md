# Dibby

A personal finance manager.

## Installation

```bash
# Install dependencies
docker-compose run --rm composer install

# Apply configuration
cp .env.dist .env
# Modify .env as needed

# Run database migrations
docker-compose run --rm --entrypoint php ./vendor/bin/doctrine-migrations migrate

# Start containers
docker-compose up -d
```
