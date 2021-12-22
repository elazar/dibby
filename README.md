# Dibby

A personal finance manager.

## Installation

### Using Docker

```bash
# Apply default configuration
cp config.php.dist config.php

# Start containers
docker-compose up -d

# Install dependencies
docker-compose exec php composer install

# Run database migrations
docker-compose exec php composer run-script migrate
```

### Manual

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
composer run-script migrate
```

> **NOTE**: If you're using a virtual host configured without SSL, be sure to
> disable the configuration setting to secure the session.
>
> For environmental variable configuration: set `DIBBY_SESSION_SECURE` to an empty string.
> For PHP file configuration: set `$config['session']['secure']` to `false`.

## Usage

Assuming you've performed the installation using Docker, navigate to
[http://localhost:8080](http://localhost:8080) to continue. Otherwise, navigate
to the appropriate host and port for your hosting setup.
