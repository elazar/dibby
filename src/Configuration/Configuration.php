<?php

namespace Elazar\Dibby\Configuration;

use Elazar\Dibby\Database\DatabaseConfiguration;

class Configuration
{
    public function __construct(
        private DatabaseConfiguration $databaseReadConfiguration,
        private DatabaseConfiguration $databaseWriteConfiguration,
        private string $baseUrl,
        private string $fromEmail,
        private string $sessionKey,
        private string $sessionCookie,
        private string $sessionTimeToLive,
        private bool $sessionSecure,
        private string $resetTokenTimeToLive,
        private string $smtpHost,
        private int $smtpPort,
    ) { }

    public function getDatabaseReadConfiguration(): DatabaseConfiguration
    {
        return $this->databaseReadConfiguration;
    }

    public function getDatabaseWriteConfiguration(): DatabaseConfiguration
    {
        return $this->databaseWriteConfiguration;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getFromEmail(): string
    {
        return $this->fromEmail;
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    public function getSessionCookie(): string
    {
        return $this->sessionCookie;
    }

    public function getSessionTimeToLive(): string
    {
        return $this->sessionTimeToLive;
    }

    public function getSessionSecure(): bool
    {
        return $this->sessionSecure;
    }

    public function getResetTokenTimeToLive(): string
    {
        return $this->resetTokenTimeToLive;
    }

    public function getSmtpHost(): string
    {
        return $this->smtpHost;
    }

    public function getSmtpPort(): int
    {
        return $this->smtpPort;
    }
}
