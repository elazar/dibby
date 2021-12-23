<?php

namespace Elazar\Dibby\Account;

use Elazar\Dibby\{
    Database\DoctrineConnectionFactory,
    Exception,
};
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Throwable;

class DoctrineAccountRepository implements AccountRepository
{
    private const TABLE = 'account';

    public function __construct(
        private DoctrineConnectionFactory $connectionFactory,
        private LoggerInterface $logger,
    ) { }

    /**
     * {@inheritdoc}
     */
    public function persistAccount(Account $account): Account
    {
        /** @var string */
        $accountName = $account->getName();
        $existing = $this->getAccountByName($accountName);
        if ($account->getId() !== $existing->getId()) {
            throw Exception::accountExists($accountName);
        }

        if ($account->getId() === null) {
            $method = 'insert';
            $account = $account->withId(Uuid::uuid4());
            $args = [];
        } else {
            $method = 'update';
            $args = [['id' => $account->getId()]];
        }

        try {
            $connection = $this->connectionFactory->getWriteConnection();
            $table = $connection->quoteIdentifier(self::TABLE);
            $data = $account->toArray();
            $connection->$method($table, $data, ...$args);
            return $account;
        } catch (Throwable $error) {
            $this->logger->error('Error persisting account', [
                'account_id' => $account->getId(),
                'account_name' => $accountName,
                'error' => $error,
            ]);
            throw Exception::databaseUnknownError($error);
        }
    }

    /**
     * @return Account[]
     */
    public function getAccounts(): array
    {
        try {
            $connection = $this->connectionFactory->getReadConnection();
            $table = $connection->quoteIdentifier(self::TABLE);
            $results = $connection->executeQuery(
                <<<EOS
                SELECT
                    *
                FROM
                    $table
                ORDER BY
                    name
                EOS
            );
            /** @var Account[] */
            $accounts = [];
            while (
                /** @var array<string, string> $row */
                $row = $results->fetchAssociative()
            ) {
                $accounts[] = Account::fromArray($row);
            }
            return $accounts;
        } catch (Throwable $error) {
            $this->logger->warning('Error fetching accounts', [
                'error' => $error,
            ]);
            throw $error;
        }
    }

    public function getAccountById(string $id): Account
    {
        $account = $this->getAccountBy('id', $id);
        if (empty($account->getId())) {
            throw Exception::accountNotFound($id);
        }
        return $account;
    }

    public function getAccountByName(string $name): Account
    {
        return $this->getAccountBy('name', $name)->withName($name);
    }

    private function getAccountBy(string $field, string $value): Account
    {
        try {
            $connection = $this->connectionFactory->getReadConnection();
            $column = $connection->quoteIdentifier($field);
            $table = $connection->quoteIdentifier(self::TABLE);
            /** @var array<string, string> */
            $data = $connection->fetchAssociative(
                <<<EOS
                SELECT
                    *
                FROM
                    $table
                WHERE
                    $column = ?
                EOS,
                [$value],
            );
        } catch (Throwable $error) {
            $this->logger->error('Error checking for existing account', [
                'account_name' => $name,
                'error' => $error,
            ]);
            throw Exception::databaseUnknownError($error);
        }

        return Account::fromArray($data ?: [$field => $value]);
    }
}
