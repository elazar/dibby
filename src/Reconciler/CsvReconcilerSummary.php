<?php

namespace Elazar\Dibby\Reconciler;

use DateTimeImmutable;
use Elazar\Dibby\Csv\CsvTransaction;
use Elazar\Dibby\{
    Transaction\Transaction as DibbyTransaction,
    Transaction\TransactionInterface,
};

class CsvReconcilerSummary
{
    /**
     * @param CsvTransaction[] $csvTransactionsMissingFromDibby
     * @param DibbyTransaction[] $dibbyTransactionsMissingFromCsv
     * @param CsvTransaction[] $csvTransactionsWithDifferingCounts
     * @param DibbyTransaction[] $dibbyTransactionsWithDifferingCounts
     */
    public function __construct(
        private array $csvTransactionsMissingFromDibby,
        private array $dibbyTransactionsMissingFromCsv,
        private array $csvTransactionsWithDifferingCounts,
        private array $dibbyTransactionsWithDifferingCounts,
    ) { }

    /**
     * @return CsvTransaction[]
     */
    public function getCsvTransactionsMissingFromDibby(): array
    {
        return $this->csvTransactionsMissingFromDibby;
    }

    /**
     * @return DibbyTransaction[]
     */
    public function getDibbyTransactionsMissingFromCsv(): array
    {
        return $this->dibbyTransactionsMissingFromCsv;
    }

    /**
     * @return CsvTransaction[]
     */
    public function getCsvTransactionsWithDifferingCounts(): array
    {
        return $this->csvTransactionsWithDifferingCounts;
    }

    /**
     * @return DibbyTransaction[]
     */
    public function getDibbyTransactionsWithDifferentCounts(): array
    {
        return $this->dibbyTransactionsWithDifferingCounts;
    }
}
