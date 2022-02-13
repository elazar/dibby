<?php

namespace Elazar\Dibby\Reconciler;

use DateTimeImmutable;
use Elazar\Dibby\Csv\{
    ChaseCsvParser,
    CsvTransaction,
};
use Elazar\Dibby\Reconciler\{
    CsvReconciler,
    CsvReconcilerSummary,
};
use Elazar\Dibby\Transaction\{
    Transaction,
    TransactionCriteria,
    TransactionRepository,
};

class CsvReconcilerService
{
    public function __construct(
        private ChaseCsvParser $csvParser,
        private CsvReconciler $csvReconciler,
        private TransactionRepository $transactionRepository,
    ) { }

    public function reconcile(string $csv, string $accountId): CsvReconcilerSummary
    {
        $csvTransactions = $this->csvParser->parseString($csv);
        $dateStart = $this->getEarliestTransactionDate($csvTransactions);
        $criteria = new TransactionCriteria(
            dateStart: $dateStart,
            debitAccountId: $accountId,
            creditAccountId: $accountId,
        );
        $dibbyTransactions = $this->transactionRepository->getTransactions($criteria);
        // Filter out pending transactions
        $dibbyTransactions = array_filter(
          $dibbyTransactions,
          fn(Transaction $transaction): bool => $transaction->getDate() !== null,
        );
        return $this->csvReconciler->reconcile($dibbyTransactions, $csvTransactions);
    }

    /**
     * @param CsvTransaction[] $transactions
     */
    private function getEarliestTransactionDate(array $transactions): DateTimeImmutable
    {
        return array_reduce(
            $transactions,
            function (
                ?DateTimeImmutable $earliestDate,
                CsvTransaction $transaction,
            ): DateTimeImmutable {
                if ($earliestDate === null) {
                    return $transaction->getDate();
                }
                return min($earliestDate, $transaction->getDate());
            },
            null,
        );
    }
}
