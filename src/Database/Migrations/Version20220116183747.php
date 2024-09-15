<?php

namespace Elazar\Dibby\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;

final class Version20220116183747 extends BaseMigration
{
    public function getDescription(): string
    {
        return 'Make transaction dates nullable';
    }

    public function up(Schema $schema): void
    {
        $toSchema = clone $schema;
        $transaction = $toSchema->getTable('transaction');
        $transaction->changeColumn('date', ['notnull' => false]);
        $this->migrate($schema, $toSchema);
    }

    public function down(Schema $schema): void
    {
        $toSchema = clone $schema;
        $transaction = $toSchema->getTable('transaction');
        $transaction->changeColumn('date', ['notnull' => true]);
        $this->migrate($schema, $toSchema);
    }
}
