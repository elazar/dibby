<?php

namespace Elazar\Dibby\Reconciler;

use Elazar\Dibby\{
    Importer\ImportedTransaction,
    Transaction\Transaction as DibbyTransaction,
    Transaction\TransactionInterface,
};

class ImporterReconciler
{
    /**
     * @param DibbyTransaction[] $dibbyTransactions
     * @param ImportedTransaction[] $importedTransactions
     */
    public function reconcile(
        array $dibbyTransactions,
        array $importedTransactions,
    ): ReconcilerSummary {
        $dibbyByAmount = $this->getTransactionsByAmount($dibbyTransactions);
        $importedByAmount = $this->getTransactionsByAmount($importedTransactions);

        $importedTransactionsMissingFromDibby = $this->getMissingTransactions($importedByAmount, $dibbyByAmount);
        $dibbyTransactionsMissingFromImport = $this->getMissingTransactions($dibbyByAmount, $importedByAmount);

        $importedTransactionsWithDifferingCounts = $this->getTransactionsWithDifferingCounts($importedByAmount, $dibbyByAmount);
        $dibbyTransactionsWithDifferingCounts = $this->getTransactionsWithDifferingCounts($dibbyByAmount, $importedByAmount);

        return new ReconcilerSummary(
            $importedTransactionsMissingFromDibby,
            $dibbyTransactionsMissingFromImport,
            $importedTransactionsWithDifferingCounts,
            $dibbyTransactionsWithDifferingCounts,
        );
    }

    private function getTransactionsByAmount(array $transactions): array
    {
        return array_reduce(
            $transactions,
            function (array $byAmount, TransactionInterface $transaction): array {
                $amount = (string) abs($transaction->getAmount());
                $byAmount[$amount] = $byAmount[$amount] ?? [];
                $byAmount[$amount][] = $transaction;
                return $byAmount;
            },
            [],
        );
    }

    private function getMissingTransactions(array $compare, array $to): array
    {
        $missing = [];
        foreach ($compare as $amount => $transactions) {
            if (count($transactions) === 1 && !isset($to[$amount])) {
                $missing[] = reset($transactions);
            }
        }
        return $missing;
    }

    private function getTransactionsWithDifferingCounts(array $compare, array $to): array
    {
        $different = [];
        foreach ($compare as $amount => $transactions) {
            if (isset($to[$amount]) && count($transactions) !== count($to[$amount])) {
                $different = array_merge($different, $transactions);
            }
        }
        return $different;
    }
}
