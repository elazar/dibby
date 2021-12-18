<?php

namespace Elazar\Dibby\Transaction;

use DateTimeImmutable;

use Elazar\Dibby\Account\AccountService;

class TransactionService
{
    public function __construct(
        private AccountService $accountService,
    ) { }

    /**
     * @param array<string, string> $data
     */
    public function fromArray(array $data): Transaction
    {
        $amount = (float) ($data['amount'] ?? 0.0);
        $debitAccount = $this->accountService->getOrCreateAccountByName($data['debit_account']);
        $creditAccount = $this->accountService->getOrCreateAccountByName($data['credit_account']);
        $date = new DateTimeImmutable($data['date'] ?? 'today');

        $transaction = new Transaction(
            $amount,
            $debitAccount,
            $creditAccount,
            $date,
        );

        if (isset($data['id'])) {
            $transaction = $transaction->withId($data['id']);
        }

        if (isset($data['description'])) {
            $transaction = $transaction->withDescription($data['description']);
        }

        return $transaction;
    }

    public function persistTransaction(Transaction $transaction): Transaction
    {
    }
}
