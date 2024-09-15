<?php

namespace Elazar\Dibby\Database;

use Doctrine\DBAL\Configuration as DoctrineConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware;
use Elazar\Dibby\Configuration\Configuration;
use Elazar\Dibby\Exception;
use Throwable;

class DoctrineConnectionFactory implements DatabaseConnectionFactory
{
    private ?Connection $readConnection = null;
    private ?Connection $writeConnection = null;

    public function __construct(
        private DatabaseConfiguration $readConfiguration,
        private DatabaseConfiguration $writeConfiguration,
        private DoctrineConfiguration $doctrineConfiguration,
    ) { }

    /**
     * @throws \Elazar\Dibby\Exception
     * @return Connection
     */
    public function getReadConnection()
    {
        if ($this->readConnection === null) {
            $this->readConnection = $this->getConnection($this->readConfiguration);
        }
        return $this->readConnection;
    }

    /**
     * @throws \Elazar\Dibby\Exception
     * @return Connection
     */
    public function getWriteConnection()
    {
        if ($this->writeConnection === null) {
            $this->writeConnection = $this->getConnection($this->writeConfiguration);

            // Switch subsequent reads to the write connection to avoid
            // fetching stale data due to delays in replication
            $this->readConnection = $this->writeConnection;
        }
        return $this->writeConnection;
    }

    private function getConnection(DatabaseConfiguration $configuration): Connection
    {
        if ($configuration->getDriver() === 'pdo_sqlite'
            && empty($configuration->getHost())) {
            throw Exception::databaseMissingSqlitePath();
        }

        $params = [
            'dbname' => $configuration->getName(),
            'user' => $configuration->getUser(),
            'password' => $configuration->getPassword(),
            'host' => $configuration->getHost(),
            'driver' => $configuration->getDriver(),
            'port' => $configuration->getPort(),
        ];

        try {
            $connection = DriverManager::getConnection($params, $this->doctrineConfiguration);
            $connection->connect();
            return $connection;
        } catch (Throwable $error) {
            $message = $error->getMessage();
            if (strpos($message, "'driver' or 'driverClass' are mandatory") !== false) {
                throw Exception::databaseMissingDriver($error);
            }
            if (strpos($message, 'could not connect to server') !== false) {
                throw Exception::databaseConnectionFailed($error);
            }
            if (strpos($message, 'Doctrine currently supports only the following drivers') !== false) {
                throw Exception::databaseDriverUnavailable($configuration->getDriver(), $error);
            }
            throw Exception::databaseUnknownError($error);
        }
    }
}
