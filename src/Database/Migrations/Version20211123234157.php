<?php

declare(strict_types=1);

namespace Elazar\Dibby\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;

final class Version20211123234157 extends BaseMigration
{
    public function getDescription(): string
    {
        return 'Add name column to user table';
    }

    public function up(Schema $schema): void
    {
        $toSchema = clone $schema;
        $table = $toSchema->getTable('user');
        $table->addColumn('name', 'string');
        $this->migrate($schema, $toSchema);
    }

    public function down(Schema $schema): void
    {
        $toSchema = clone $schema;
        $table = $toSchema->getTable('user');
        $table->dropColumn('name');
        $this->migrate($schema, $toSchema);
    }
}
