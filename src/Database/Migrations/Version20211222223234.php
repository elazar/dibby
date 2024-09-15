<?php

namespace Elazar\Dibby\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;

final class Version20211222223234 extends BaseMigration
{
    public function getDescription(): string
    {
        return 'Create transaction table';
    }

    public function up(Schema $schema): void
    {
        $account = $schema->getTable('account');

        $toSchema = clone $schema;
        $table = $toSchema->createTable('transaction');
        $table->addColumn('id', 'guid');
        $table->addColumn('amount', 'decimal', ['scale' => 2]);
        $table->addColumn('debit_account_id', 'guid');
        $table->addColumn('credit_account_id', 'guid');
        $table->addColumn('date', 'date_immutable');
        $table->addColumn('description', 'string', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['amount']);
        $table->addIndex(['date']);
        $table->addIndex(['debit_account_id']);
        $table->addIndex(['credit_account_id']);
        $table->addForeignKeyConstraint($account, ['debit_account_id'], ['id']);
        $table->addForeignKeyConstraint($account, ['credit_account_id'], ['id']);

        $this->migrate($schema, $toSchema);

        $this->addSql('CREATE EXTENSION pg_trgm');
        $this->addSql('CREATE INDEX idx_description ON "transaction" USING gin (description gin_trgm_ops)');
    }

    public function down(Schema $schema): void
    {
        $toSchema = clone $schema;
        $toSchema->dropTable('transaction');
        $this->migrate($schema, $toSchema);
        $this->addSql('DROP EXTENSION pg_trgm');
    }
}
