<?php

namespace Elazar\Dibby\Database\Migrations;

use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Elazar\Dibby\Database\DoctrineConnectionFactory;

class CliConfig
{
    public function __construct(
        private DoctrineConnectionFactory $connectionFactory,
    ) { }

    public function getDependencyFactory(): DependencyFactory
    {
        return DependencyFactory::fromConnection(
            $this->getConfiguration(),
            new ExistingConnection($this->connectionFactory->getWriteConnection()),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        return $this->connectionFactory
                    ->getWriteConnection()
                    ->getParams();
    }

    private function getConfiguration(): PhpFile
    {
        return new PhpFile(__DIR__ . '/../../../migrations/config.php');
    }
}
