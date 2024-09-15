<?php

namespace Elazar\Dibby\Configuration;

use Elazar\Dibby\Database\DatabaseConfiguration;
use RuntimeException;

class EnvConfigurationFactory implements ConfigurationFactory
{
    public function getConfiguration(): Configuration
    {
        $databaseReadConfiguration = new DatabaseConfiguration(
            (string) $this->getRequired('DB_READ_DRIVER'),
            (string) $this->getRequired('DB_READ_HOST'),
            (int) $this->getRequired('DB_READ_PORT'),
            (string) $this->getRequired('DB_READ_USER'),
            (string) $this->getRequired('DB_READ_PASSWORD'),
            (string) $this->getRequired('DB_READ_NAME'),
        );

        $databaseWriteConfiguration = new DatabaseConfiguration(
            (string) $this->getRequired('DB_WRITE_DRIVER'),
            (string) $this->getRequired('DB_WRITE_HOST'),
            (int) $this->getRequired('DB_WRITE_PORT'),
            (string) $this->getRequired('DB_WRITE_USER'),
            (string) $this->getRequired('DB_WRITE_PASSWORD'),
            (string) $this->getRequired('DB_WRITE_NAME'),
        );

        return new Configuration(
            $databaseReadConfiguration,
            $databaseWriteConfiguration,
            (string) $this->getRequired('BASE_URL'),
            (string) $this->getRequired('FROM_EMAIL'),
            (string) $this->getRequired('SESSION_KEY'),
            (string) $this->getRequired('SESSION_COOKIE'),
            (string) $this->getRequired('SESSION_TTL'),
            (bool) $this->getRequired('SESSION_SECURE'),
            (string) $this->getRequired('RESET_TOKEN_TTL'),
            (string) $this->getRequired('SMTP_HOST'),
            (int) $this->getRequired('SMTP_PORT'),
            $this->getOptional('SMTP_USERNAME'),
            $this->getOptional('SMTP_PASSWORD'),
            $this->getOptional('SMTP_TLS') === 'true',
        );
    }

    private function getRequired(string $name): string
    {
        $fullName = $this->getEnvName($name);
        $value = getenv($fullName);
        if ($value === false) {
            throw new RuntimeException("Environmental variable '$fullName' is undefined");
        }
        return (string) $value;
    }

    private function getOptional(string $name, ?string $default = null): ?string
    {
        return getenv($this->getEnvName($name)) ?: $default;
    }

    private function getEnvName(string $name): string
    {
        return "DIBBY_$name";
    }
}
