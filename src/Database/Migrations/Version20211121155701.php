<?php

namespace Elazar\Dibby\Migrations;

use Doctrine\{
    DBAL\Schema\Schema,
    Migrations\AbstractMigration,
};

final class Version20211121155701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user table';
    }

    public function up(Schema $schema): void
    {
        $toSchema = clone $schema;
        $table = $toSchema->createTable('user');
        $table->addColumn('id', 'guid');
        $table->addColumn('email', 'string');
        $table->addColumn('password_hash', 'string');
        $table->addColumn('reset_token', 'string', ['notnull' => false]);
        $table->addColumn('reset_token_expiration', 'datetime_immutable', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email']);
        $this->migrate($schema, $toSchema);
    }

    public function down(Schema $schema): void
    {
        $toSchema = clone $schema;
        $toSchema->dropTable('user');
        $this->migrate($schema, $toSchema);
    }

    private function migrate(Schema $fromSchema, Schema $toSchema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $statements = $fromSchema->getMigrateToSql($toSchema, $platform);
        foreach ($statements as $sql) {
            $this->addSql($sql);
        }
    }
}
