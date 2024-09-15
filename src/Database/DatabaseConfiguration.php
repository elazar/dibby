<?php

namespace Elazar\Dibby\Database;

class DatabaseConfiguration
{
    public function __construct(
        private string $driver,
        private string $host,
        private int    $port,
        private string $user,
        private string $password,
        private string $name,
    ) { }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
