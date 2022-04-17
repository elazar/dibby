<?php

namespace Elazar\Dibby\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;

final class Version20220417155925 extends BaseMigration
{
    public function getDescription(): string
    {
        return 'Add credit limit column to accounts table';
    }

    public function up(Schema $schema): void
    {
        $toSchema = clone $schema;
        $table = $toSchema->getTable('account');
        $table->addColumn('credit_limit', 'decimal', ['scale' => 2, 'notnull' => false]);
        $this->migrate($schema, $toSchema);
    }

    public function down(Schema $schema): void
    {
        $toSchema = clone $schema;
        $table = $toSchema->getTable('account');
        $table->dropColumn('credit_limit');
        $this->migrate($schema, $toSchema);
    }
}
