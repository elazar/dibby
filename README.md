# Dibby

A personal finance manager.

## Installation

### Using Docker

```bash
# Install dependencies
docker-compose run --rm composer install

# Apply default configuration
cp config.php.dist config.php

# Start containers
docker-compose up -d

# Run database migrations
docker-compose run --rm composer run-script migrate
```

### Local

You'll need:

- A web server that is:
  - configured to run PHP
  - able to host Dibby in the document root or using a virtual host
- A [database server supported by Doctrine](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#driver)
- [Composer](https://getcomposer.org/download)

```bash
# Install dependencies
composer install

# Copy and modify the default configuration as needed for your setup
cp config.php.dist config.php
# ...

# Run database migrations
php ./vendor/bin/doctrine-migrations migrate
```

## Usage

Assuming you've performed installation on your local machine, navigate to [http://localhost:8080](http://localhost:8080) to continue. Otherwise, navigate to the appropriate host and port for your installation.
