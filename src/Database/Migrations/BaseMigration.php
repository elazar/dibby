<?php

namespace Elazar\Dibby\Database\Migrations;

use Doctrine\{
    DBAL\Schema\Schema,
    Migrations\AbstractMigration,
};

abstract class BaseMigration extends AbstractMigration
{
    protected function migrate(Schema $fromSchema, Schema $toSchema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $statements = $fromSchema->getMigrateToSql($toSchema, $platform);
        foreach ($statements as $sql) {
            $this->addSql($sql);
        }
    }
}
