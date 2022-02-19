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
        $dateStart = $this->getEarliestTransactionDate($importTransactions);
        $dateEnd = $this->getLatestTransactionDate($importTransactions);
        $criteria = new TransactionCriteria(
            dateStart: $dateStart,
            dateEnd: $dateEnd,
            debitAccountId: $accountId,
            creditAccountId: $accountId,
        );
        $dibbyTransactions = $this->transactionRepository->getTransactions($criteria);
        // Filter out pending transactions
        $dibbyTransactions = array_filter(
          $dibbyTransactions,
          fn(Transaction $transaction): bool => !$transaction->isPending(),
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
                ?DateTimeImmutable $earliestDate,
                ImportedTransaction $transaction,
            ) use ($comparator): DateTimeImmutable {
                if ($earliestDate === null) {
                    return $transaction->getDate();
                }
                return $comparator($earliestDate, $transaction->getDate());
            },
            null,
        );
    }
}
