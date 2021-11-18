<?php

namespace Elazar\Dibby\Configuration;

use Elazar\Dibby\Database\DatabaseConfiguration;

class Configuration
{
    public function __construct(
        private DatabaseConfiguration $databaseReadConfiguration,
        private DatabaseConfiguration $databaseWriteConfiguration,
        private string $sessionKey,
        private int $sessionTimeToLive,
    ) { }

    public function getDatabaseReadConfiguration(): DatabaseConfiguration
    {
        return $this->databaseReadConfiguration;
    }

    public function getDatabaseWriteConfiguration(): DatabaseConfiguration
    {
        return $this->databaseWriteConfiguration;
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    public function getSessionTimeToLive(): int
    {
        return $this->sessionTimeToLive;
    }
}
