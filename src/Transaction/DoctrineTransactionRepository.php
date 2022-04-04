<?php

namespace Elazar\Dibby\Transaction;

use DateTimeImmutable;
use Elazar\Dibby\{
    Account\Account,
    Account\AccountRepository,
    Database\DoctrineConnectionFactory,
    Exception,
};
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Throwable;

class DoctrineTransactionRepository implements TransactionRepository
{
    private const TABLE = 'transaction';

    public function __construct(
        private DoctrineConnectionFactory $connectionFactory,
        private AccountRepository $accountRepository,
        private LoggerInterface $logger,
    ) { }

    public function getTransactionById(string $transactionId): Transaction
    {
        try {
            $connection = $this->connectionFactory->getReadConnection();
            $table = $connection->quoteIdentifier(self::TABLE);
            $result = $connection->executeQuery(
                <<<EOS
                SELECT
                    *
                FROM
                    $table
                WHERE
                    id = ?
                EOS,
                [$transactionId],
            );
            $data = $result->fetchAssociative();
            if ($data === false) {
                throw Exception::transactionNotFound($transactionId);
            }
            return $this->fromArray($data);
        } catch (Throwable $error) {
            $this->logger->error('Error getting transaction', [
                'transaction_id' => $transactionId,
                'error' => $error,
            ]);
            throw $error;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactions(TransactionCriteria $criteria): array
    {
        try {
            $connection = $this->connectionFactory->getReadConnection();
            $sql = $connection->createQueryBuilder()
                              ->select('*')
                              ->from(self::TABLE);
            $where = [];
            if ($description = $criteria->getDescription()) {
                $where[] = 'description ILIKE :description';
                $sql->setParameter('description', "%$description%");
            }
            if ($amountStart = $criteria->getAmountStart()) {
                $where[] = 'amount >= :amountStart';
                $sql->setParameter('amountStart', $amountStart);
            }
            if ($amountEnd = $criteria->getAmountEnd()) {
                $where[] = 'amount <= :amountEnd';
                $sql->setParameter('amountEnd', $amountEnd);
            }
            if ($debitAccountId = $criteria->getDebitAccountId()) {
                $sql->setParameter('debitAccountId', $debitAccountId);
            }
            if ($creditAccountId = $criteria->getDebitAccountId()) {
                $sql->setParameter('creditAccountId', $creditAccountId);
            }
            if ($debitAccountId) {
                $clause = 'debit_account_id = :debitAccountId';
                if ($debitAccountId === $creditAccountId) {
                    $clause .= ' OR credit_account_id = :creditAccountId';
                }
                $where[] = $clause;
            }
            if ($creditAccountId && $creditAccountId !== $debitAccountId) {
                $where[] = 'credit_account_id = :creditAccountId';
            }
            $date = $connection->quoteIdentifier('date');
            if ($dateStart = $criteria->getDateStart()) {
                $where[] = "$date >= :dateStart or $date is null";
                $sql->setParameter('dateStart', $dateStart->format('Y-m-d'));
            }
            if ($dateEnd = $criteria->getDateEnd()) {
                $where[] = "$date <= :dateEnd or $date is null";
                $sql->setParameter('dateEnd', $dateEnd->format('Y-m-d'));
            }
            $sql->where($sql->expr()->and(...$where));
            $sql->orderBy('date is not null and date >= current_date', 'desc');
            $sql->addOrderBy('date', 'desc nulls first');
            $result = $sql->executeQuery();
            $transactions = [];
            foreach ($result->iterateAssociative() as $row) {
                $transactions[] = $this->fromArray($row);
            }
            return $transactions;
        } catch (Throwable $error) {
            $this->logger->error('Error getting transactions', [
                'criteria' => $criteria,
                'error' => $error,
            ]);
            throw $error;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function persistTransaction(Transaction $transaction): Transaction
    {
        if ($transaction->getId() === null) {
            $method = 'insert';
            $transaction = $transaction->withId(Uuid::uuid4());
            $args = [];
        } else {
            $method = 'update';
            $args = [['id' => $transaction->getId()]];
        }

        try {
            $connection = $this->connectionFactory->getWriteConnection();
            $table = $connection->quoteIdentifier(self::TABLE);
            $data = $transaction->toArray();
            $connection->$method($table, $data, ...$args);
            return $transaction;
        } catch (Throwable $error) {
            $this->logger->error('Error persisting transaction', [
                'transaction' => $transaction->toArray(),
                'error' => $error,
            ]);
            throw $error;
        }
    }

    private function fromArray(array $data): Transaction
    {
        $debitAccount = $this->accountRepository->getAccountById($data['debit_account_id']);
        $creditAccount = $this->accountRepository->getAccountById($data['credit_account_id']);
        return new Transaction(
            amount: $data['amount'],
            debitAccount: $debitAccount,
            creditAccount: $creditAccount,
            date: is_string($data['date']) ? new DateTimeImmutable($data['date']) : $data['date'],
            id: $data['id'],
            description: $data['description'],
        );
    }

    public function getAccountBalance(string $accountId): float
    {
        try {
            $connection = $this->connectionFactory->getReadConnection();
            $table = $connection->quoteIdentifier(self::TABLE);
            $debits = $connection->fetchOne(
                <<<EOS
                SELECT SUM(amount)
                FROM $table
                WHERE debit_account_id = ?
                EOS,
                [$accountId]
            );
            $credits = $connection->fetchOne(
                <<<EOS
                SELECT SUM(amount)
                FROM $table
                WHERE credit_account_id = ?
                EOS,
                [$accountId]
            );
            return $credits - $debits;
        } catch (Throwable $error) {
            $this->logger->error('Error getting account balance', [
                'account_id' => $accountId,
                'error' => $error,
            ]);
            throw Exception::databaseUnknownError($error);
        }
    }

    public function getAccountTransactionCount(string $accountId): int
    {
        try {
            $connection = $this->connectionFactory->getReadConnection();
            $table = $connection->quoteIdentifier(self::TABLE);
            $result = $connection->fetchOne(
                <<<EOS
                SELECT COUNT(*)
                FROM $table
                WHERE debit_account_id = :id
                OR credit_account_id = :id
                EOS,
                ['id' => $accountId]
            );
            return $result === false ? 0 : (int) $result;
        } catch (Throwable $error) {
            $this->logger->error('Error getting account transaction count', [
                'account_id' => $accountId,
                'error' => $error,
            ]);
            throw Exception::databaseUnknownError($error);
        }
    }

    public function deleteTransactionById(string $transactionId): void
    {
        try {
            $connection = $this->connectionFactory->getWriteConnection();
            $connection->delete(self::TABLE, ['id' => $transactionId]);
        } catch (Throwable $error) {
            $this->logger->error('Error deleting transaction', [
                'transaction_id' => $transactionId,
                'error' => $error,
            ]);
            throw Exception::databaseUnknownError($error);
        }
    }
}
