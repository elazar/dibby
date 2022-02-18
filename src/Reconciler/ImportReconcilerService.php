<?php

namespace Elazar\Dibby\Reconciler;

use DateTimeImmutable;
use Elazar\Dibby\Importer\{
    Importer,
    ImportedTransaction,
};
use Elazar\Dibby\Reconciler\{
    ImportReconciler,
    ImportReconcilerSummary,
};
use Elazar\Dibby\Transaction\{
    Transaction,
    TransactionCriteria,
    TransactionRepository,
};

class CsvReconcilerService
{
    public function __construct(
        private Importer $importer,
        private ImportReconciler $importReconciler,
        private TransactionRepository $transactionRepository,
    ) { }

    public function reconcile(string $data, string $accountId): CsvReconcilerSummary
    {
        $importTransactions = $this->importer->import($data);
        $dateStart = $this->getEarliestTransactionDate($importTransactions);
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
        return $this->importReconciler->reconcile($dibbyTransactions, $importTransactions);
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
                ImportedTransaction $transaction,
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
