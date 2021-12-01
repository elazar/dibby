<?php

namespace Elazar\Dibby\Transaction;

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
}
