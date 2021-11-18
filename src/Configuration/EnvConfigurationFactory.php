<?php

namespace Elazar\Dibby\Configuration;

use Elazar\Dibby\Database\DatabaseConfiguration;

class EnvConfigurationFactory implements ConfigurationFactory
{
    public function getConfiguration(): Configuration
    {
        $databaseReadConfiguration = new DatabaseConfiguration(
            $this->getEnv('DB_READ_DRIVER'),
            $this->getEnv('DB_READ_HOST'),
            (int) $this->getEnv('DB_READ_PORT'),
            $this->getEnv('DB_READ_USER'),
            $this->getEnv('DB_READ_PASSWORD'),
            $this->getEnv('DB_READ_NAME'),
        );

        $databaseWriteConfiguration = new DatabaseConfiguration(
            $this->getEnv('DB_WRITE_DRIVER'),
            $this->getEnv('DB_WRITE_HOST'),
            (int) $this->getEnv('DB_WRITE_PORT'),
            $this->getEnv('DB_WRITE_USER'),
            $this->getEnv('DB_WRITE_PASSWORD'),
            $this->getEnv('DB_WRITE_NAME'),
        );

        return new Configuration(
            $databaseReadConfiguration,
            $databaseWriteConfiguration,
            $this->getEnv('SESSION_KEY'),
            (int) $this->getEnv('SESSION_TTL'),
        );
    }

    private function getEnv(string $name): string
    {
        return getenv($name) ?: '';
    }
}
