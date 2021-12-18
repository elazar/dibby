<?php

namespace Elazar\Dibby\Transaction;

use Elazar\Dibby\Account\Account;

interface TransactionRepository
{
    /**
     * @return Transaction[]
     */
    public function getTransactions(): array;

    /**
     * @return Transaction[]
     */
    public function getTransactionsByAccount(Account $account): array;

    /**
     * Should call withId() on $transaction and pass it a value for a unique
     * identifier for the transaction that is appropriate for storage in this
     * repository implentation.
     *
     * @throws \Elazar\Dibby\Exception if transaction cannot be persisted
     */
    public function persistTransaction(Transaction $transaction): Transaction;
}
