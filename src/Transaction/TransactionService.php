<?php

namespace Elazar\Dibby\Transaction;

use DateTimeImmutable;
use Elazar\Dibby\{
    Account\Account,
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
        if (!isset($data['amount'])) {
            throw Exception::invalidInput('Amount is required');
        }
        if (empty($data['debit_account'])) {
            throw Exception::invalidInput('Debit Account is required');
        }
        if (empty($data['credit_account'])) {
            throw Exception::invalidInput('Credit Account is required');
        }
        if (!empty($data['date'])) {
            try {
                $date = new DateTimeImmutable($data['date']);
            } catch (\Exception $error) {
                $this->logger->notice('Invalid transaction date entered', [
                    'date' => $data['date'],
                    'error' => $error,
                ]);
                throw Exception::invalidInput('Date is invalid');
            }
        } else {
            $date = null;
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
        if ($transaction->getId()) {
            $existing = $this->transactionRepository->getTransactionById($transaction->getId());
        }
        $result = $this->transactionRepository->persistTransaction($transaction);
        if (isset($existing)) {
            $debitAccountId = $existing->getDebitAccount()->getId();
            if ($debitAccountId !== null && $debitAccountId !== $transaction->getDebitAccount()->getId()) {
                $this->deleteAccountIfEmpty($debitAccountId);
            }
            $creditAccountId = $existing->getCreditAccount()->getId();
            if ($creditAccountId !== null && $creditAccountId !== $transaction->getCreditAccount()->getId()) {
                $this->deleteAccountIfEmpty($creditAccountId);
            }
        }
        return $result;
    }

    public function deleteTransaction(Transaction $transaction): void
    {
        /** @var string $transactionId */
        $transactionId = $transaction->getId();
        /** @var string $debitAccountId */
        $debitAccountId = $transaction->getDebitAccount()->getId();
        /** @var string $creditAccountId */
        $creditAccountId = $transaction->getCreditAccount()->getId();
        $this->transactionRepository->deleteTransactionById($transactionId);
        $this->deleteAccountIfEmpty($debitAccountId);
        $this->deleteAccountIfEmpty($creditAccountId);
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions(TransactionCriteria $criteria): array
    {
        if ($criteria->isEmpty()) {
            throw Exception::invalidInput('No criteria provided to filter transactions');
        }
        return $this->transactionRepository->getTransactions($criteria);
    }

    private function deleteAccountIfEmpty(string $accountId): void
    {
        $transactionCount = $this->transactionRepository->getAccountTransactionCount($accountId);
        if ($transactionCount === 0) {
            $this->accountService->deleteAccount($accountId);
        }
    }
}
