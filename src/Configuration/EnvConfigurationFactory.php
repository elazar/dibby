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
            $this->getEnv('BASE_URL'),
            $this->getEnv('FROM_EMAIL'),
            $this->getEnv('SESSION_KEY'),
            $this->getEnv('SESSION_COOKIE'),
            $this->getEnv('SESSION_TTL'),
            (bool) $this->getEnv('SESSION_SECURE'),
            $this->getEnv('RESET_TOKEN_TTL'),
            $this->getEnv('SMTP_HOST'),
            (int) $this->getEnv('SMTP_PORT'),
            $this->getEnv('SMTP_USERNAME', null),
            $this->getEnv('SMTP_PASSWORD', null),
            $this->getEnv('SMTP_TLS') === 'true',
        );
    }

    /**
     * @param mixed $default
     */
    private function getEnv(string $name, $default = ''): string
    {
        return getenv("DIBBY_$name") ?: $default;
    }
}
