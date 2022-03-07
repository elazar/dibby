<?php

namespace Elazar\Dibby\Reconciler;

use DateTimeImmutable;
use Elazar\Dibby\Importer\{
    Importer,
    ImportedTransaction,
};
use Elazar\Dibby\Reconciler\{
    ImporterReconciler,
    ImporterReconcilerSummary,
};
use Elazar\Dibby\Transaction\{
    Transaction,
    TransactionCriteria,
    TransactionInterface,
    TransactionRepository,
};

class ImporterReconcilerService
{
    public function __construct(
        private Importer $importer,
        private ImporterReconciler $importerReconciler,
        private TransactionRepository $transactionRepository,
    ) { }

    public function reconcile(string $data, string $accountId): ImporterReconcilerSummary
    {
        $importTransactions = $this->importer->import($data);
        $importDateStart = $this->getEarliestTransactionDate($importTransactions);
        $importDateEnd = $this->getLatestTransactionDate($importTransactions);

        $criteria = new TransactionCriteria(
            dateStart: $importDateStart,
            dateEnd: $importDateEnd,
            debitAccountId: $accountId,
            creditAccountId: $accountId,
        );
        $dibbyTransactions = $this->transactionRepository->getTransactions($criteria);
        $dibbyTransactions = array_filter(
          $dibbyTransactions,
          fn(Transaction $transaction): bool => !$transaction->isPending(),
        );

        $dibbyDateStart = $this->getEarliestTransactionDate($dibbyTransactions);
        $dibbyDateEnd = $this->getLatestTransactionDate($dibbyTransactions);
        $latestDateStart = max($importDateStart, $dibbyDateStart);
        $earliestDateEnd = min($importDateEnd, $dibbyDateEnd);
        $importTransactions = array_filter(
            $importTransactions,
            fn(ImportedTransaction $transaction): bool =>
                $transaction->getDate() >= $latestDateStart && $transaction->getDate() <= $earliestDateEnd
        );

        return $this->importerReconciler->reconcile($dibbyTransactions, $importTransactions);
    }

    /**
     * @param ImportedTransaction[] $transactions
     */
    private function getEarliestTransactionDate(array $transactions): DateTimeImmutable
    {
        return $this->compareTransactionDates(
            $transactions,
            fn($a, $b) => min($a, $b),
        );
    }

    /**
     * @param ImportedTransaction[] $transactions
     */
    private function getLatestTransactionDate(array $transactions): DateTimeImmutable
    {
        return $this->compareTransactionDates(
            $transactions,
            fn($a, $b) => max($a, $b),
        );
    }

    /**
     * @param ImportedTransaction[] $transactions
     */
    private function compareTransactionDates(
        array $transactions,
        callable $comparator,
    ): DateTimeImmutable {
        return array_reduce(
            $transactions,
            function (
                ?DateTimeImmutable $date,
                TransactionInterface $transaction,
            ) use ($comparator): DateTimeImmutable {
                return $date === null
                    ? $transaction->getDate()
                    : $comparator($date, $transaction->getDate());
            },
            null,
        );
    }
}
