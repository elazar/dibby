<?php

namespace Elazar\Dibby;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class DatabaseConnectionFactory
{
    public function __construct(
        private array $env,
    ) { }

    public function getConnection(): Connection
    {
        $params = [
            'dbname' => $this->env['DB_NAME'],
            'user' => $this->env['DB_USER'],
            'password' => $this->env['DB_PASSWORD'],
            'host' => $this->env['DB_HOST'],
            'driver' => $this->env['DB_DRIVER'],
        ];

        if (isset($this->env['DB_PORT'])) {
            $params['port'] = $this->env['DB_PORT'];
        }

        return DriverManager::getConnection($params);
    }
}
