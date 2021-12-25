<?php

namespace Elazar\Dibby\Transaction;

use DateTimeImmutable;
use Elazar\Dibby\{
    Account\Account,
    Account\AccountRepository,
    Database\DoctrineConnectionFactory,
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
            if ($description = $criteria->getDescription()) {
                $sql->where('description ILIKE :description')
                    ->setParameter('description', "%$description%");
            }
            if ($amountStart = $criteria->getAmountStart()) {
                $sql->where('amount >= :amountStart')
                    ->setParameter('amountStart', $amountStart);
            }
            if ($amountEnd = $criteria->getAmountEnd()) {
                $sql->where('amount <= :amountEnd')
                    ->setParameter('amountEnd', $amountEnd);
            }
            if ($debitAccountId = $criteria->getDebitAccountId()) {
                $sql->where('debit_account_id = :debitAccountId')
                    ->setParameter('debitAccountId', $debitAccountId);
            }
            if ($creditAccountId = $criteria->getDebitAccountId()) {
                $sql->where('credit_account_id = :creditAccountId')
                    ->setParameter('creditAccountId', $creditAccountId);
            }
            $date = $connection->quoteIdentifier('date');
            if ($dateStart = $criteria->getDateStart()) {
                $sql->where("$date >= :dateStart")
                    ->setParameter('dateStart', $dateStart->format('Y-m-d'));
            }
            if ($dateEnd = $criteria->getDateEnd()) {
                $sql->where("$date <= :dateEnd")
                    ->setParameter('dateEnd', $dateEnd->format('Y-m-d'));
            }
            $sql->orderBy('date', 'desc');
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
            date: new DateTimeImmutable($data['date']),
            id: $data['id'],
            description: $data['description'],
        );
    }
}
