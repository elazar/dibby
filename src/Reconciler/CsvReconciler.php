<?php

namespace Elazar\Dibby\Reconciler;

use Elazar\Dibby\{
    Csv\CsvTransaction,
    Transaction\Transaction as DibbyTransaction,
    Transaction\TransactionInterface,
};

class CsvReconciler
{
    /**
     * @param DibbyTransaction[] $dibbyTransactions
     * @param CsvTransaction[] $csvTransactions
     */
    public function reconcile(
        array $dibbyTransactions,
        array $csvTransactions,
    ): CsvReconcilerSummary {
        $dibbyByAmount = $this->getTransactionsByAmount($dibbyTransactions);
        $csvByAmount = $this->getTransactionsByAmount($csvTransactions);

        $csvTransactionsMissingFromDibby = $this->getMissingTransactions($csvByAmount, $dibbyByAmount);
        $dibbyTransactionsMissingFromCsv = $this->getMissingTransactions($dibbyByAmount, $csvByAmount);

        $csvTransactionsWithDifferingCounts = $this->getTransactionsWithDifferingCounts($csvByAmount, $dibbyByAmount);
        $dibbyTransactionsWithDifferingCounts = $this->getTransactionsWithDifferingCounts($dibbyByAmount, $csvByAmount);

        return new CsvReconcilerSummary(
            $csvTransactionsMissingFromDibby,
            $dibbyTransactionsMissingFromCsv,
            $csvTransactionsWithDifferingCounts,
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
