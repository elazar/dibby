<?php

namespace Elazar\Dibby\Transaction;

use ArrayObject;
use DateTimeImmutable;
use Elazar\Dibby\{
    Account\Account,
    Account\AccountService,
    Exception,
};
use Psr\Log\LoggerInterface;
use SplObjectStorage;

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

    /**
     * @param Transaction[] $transactions
     */
    public function getSummary(array $transactions): TransactionSummary
    {
        $map = new class extends SplObjectStorage {
            public function getHash(object $o): string {
                return $o instanceof DateTimeImmutable ? $o->format('Ymd') : spl_object_hash($o);
            }
        };
        $null = new \stdClass;

        $byDate = array_reduce(
            $transactions,
            function ($map, Transaction $transaction) use ($null) {
                $date = $transaction->getDate() ?: $null;
                if (!isset($map[$date])) {
                    $map[$date] = new ArrayObject;
                }
                $map[$date]->append($transaction);
                return $map;
            },
            $map,
        );

        $rows = [];
        $summaryCount = 0;
        $summaryTotal = 0.0;
        foreach ($byDate as $date) {
            $dateTransactions = $map[$date];
            $count = count($dateTransactions);
            $total = array_sum(
                array_map(
                    fn(Transaction $transaction): float => $transaction->getAmount(),
                    $dateTransactions->getArrayCopy(),
                )
            );
            $rows[] = new TransactionSummaryRow($date === $null ? null : $date, $count, $total);
            $summaryCount += $count;
            $summaryTotal += $total;
        }

        usort(
            $rows,
            function (
                TransactionSummaryRow $a,
                TransactionSummaryRow $b,
            ): int {
                if ($a->getDate() === null) {
                    return -1;
                }
                if ($b->getDate() === null) {
                    return 1;
                }
                return $b->getDate() <=> $a->getDate();
            }
        );

        return new TransactionSummary($rows, $summaryCount, $summaryTotal);
    }

    private function deleteAccountIfEmpty(string $accountId): void
    {
        $transactionCount = $this->transactionRepository->getAccountTransactionCount($accountId);
        if ($transactionCount === 0) {
            $this->accountService->deleteAccount($accountId);
        }
    }
}
