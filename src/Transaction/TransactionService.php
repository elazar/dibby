<?php

namespace Elazar\Dibby\Transaction;

use DateTimeImmutable;
use Elazar\Dibby\{
    Account\AccountService,
    Exception,
};
use Psr\Log\LoggerInterface;

class TransactionService
{
    public function __construct(
        private AccountService $accountService,
        private TransactionRepository $transactionRepository,
        private LoggerInterface $logger,
    ) { }

    /**
     * @param array<string, string> $data
     */
    public function fromArray(array $data): Transaction
    {
        if (empty($data['amount'])) {
            throw Exception::invalidInput('Amount is required');
        }
        if (empty($data['debit_account'])) {
            throw Exception::invalidInput('Debit Account is required');
        }
        if (empty($data['credit_account'])) {
            throw Exception::invalidInput('Credit Account is required');
        }
        if (!isset($data['date'])) {
            throw Exception::invalidInput('Date is required');
        }
        try {
            $date = new DateTimeImmutable($data['date']);
        } catch (\Exception $error) {
            $this->logger->notice('Invalid transaction date entered', [
                'date' => $data['date'],
                'error' => $error,
            ]);
            throw Exception::invalidInput('Date is invalid');
        }

        $amount = (float) $data['amount'];
        $debitAccount = $this->accountService->getOrCreateAccountByName($data['debit_account']);
        $creditAccount = $this->accountService->getOrCreateAccountByName($data['credit_account']);

        return new Transaction(
            amount: $amount,
            debitAccount: $debitAccount,
            creditAccount: $creditAccount,
            date: $date,
            id: $data['id'] ?? null,
            description: $data['description'] ?? null,
        );
    }

    public function persistTransaction(Transaction $transaction): Transaction
    {
        if ($transaction->getAmount() < 0) {
            throw Exception::invalidInput('Transaction amount must be positive');
        }
        return $this->transactionRepository->persistTransaction($transaction);
    }

    public function getTransactions(TransactionCriteria $criteria): array
    {
        if ($criteria->isEmpty()) {
            throw Exception::invalidInput('No criteria provided to filter transactions');
        }
        return $this->transactionRepository->getTransactions($criteria);
    }
}
