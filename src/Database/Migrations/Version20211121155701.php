<?php

namespace Elazar\Dibby\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;

final class Version20211121155701 extends BaseMigration
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
}
