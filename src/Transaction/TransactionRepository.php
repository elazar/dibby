<?php

namespace Elazar\Dibby\Transaction;

use Elazar\Dibby\Account\Account;

interface TransactionRepository
{
    /**
     * @throws \Elazar\Dibby\Exception if transaction cannot be retrieved
     */
    public function getTransactionById(string $transactionId): Transaction;

    /**
     * @return Transaction[]
     */
    public function getTransactions(TransactionCriteria $criteria): array;

    /**
     * Should call withId() on $transaction and pass it a value for a unique
     * identifier for the transaction that is appropriate for storage in this
     * repository implentation.
     *
     * @throws \Elazar\Dibby\Exception if transaction cannot be persisted
     */
    public function persistTransaction(Transaction $transaction): Transaction;

    public function getAccountBalance(Account $account): float;
}
