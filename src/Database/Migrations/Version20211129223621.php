<?php

declare(strict_types=1);

namespace Elazar\Dibby\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;

final class Version20211129223621 extends BaseMigration
{
    public function getDescription(): string
    {
        return 'Create account table';
    }

    public function up(Schema $schema): void
    {
        $toSchema = clone $schema;
        $table = $toSchema->createTable('account');
        $table->addColumn('id', 'guid');
        $table->addColumn('name', 'string');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name']);
        $this->migrate($schema, $toSchema);
    }

    public function down(Schema $schema): void
    {
        $toSchema = clone $schema;
        $toSchema->dropTable('account');
        $this->migrate($schema, $toSchema);
    }
}
