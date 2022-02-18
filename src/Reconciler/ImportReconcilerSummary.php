<?php

namespace Elazar\Dibby\Reconciler;

use DateTimeImmutable;
use Elazar\Dibby\Importer\ImportedTransaction;
use Elazar\Dibby\{
    Transaction\Transaction as DibbyTransaction,
    Transaction\TransactionInterface,
};

class ReconcilerSummary
{
    /**
     * @param ImportedTransaction[] $importTransactionsMissingFromDibby
     * @param DibbyTransaction[] $dibbyTransactionsMissingFromImport
     * @param ImporterTransaction[] $importTransactionsWithDifferingCounts
     * @param DibbyTransaction[] $dibbyTransactionsWithDifferingCounts
     */
    public function __construct(
        private array $importTransactionsMissingFromDibby,
        private array $dibbyTransactionsMissingFromImport,
        private array $importTransactionsWithDifferingCounts,
        private array $dibbyTransactionsWithDifferingCounts,
    ) { }

    /**
     * @return ImportedTransaction[]
     */
    public function getImportTransactionsMissingFromDibby(): array
    {
        return $this->importTransactionsMissingFromDibby;
    }

    /**
     * @return DibbyTransaction[]
     */
    public function getDibbyTransactionsMissingFromImport(): array
    {
        return $this->dibbyTransactionsMissingFromImport;
    }

    /**
     * @return ImportedTransaction[]
     */
    public function getImportTransactionsWithDifferingCounts(): array
    {
        return $this->importTransactionsWithDifferingCounts;
    }

    /**
     * @return DibbyTransaction[]
     */
    public function getDibbyTransactionsWithDifferentCounts(): array
    {
        return $this->dibbyTransactionsWithDifferingCounts;
    }
}
