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
        try {
            $connection = $this->connectionFactory->getReadConnection();
            $table = $connection->quoteIdentifier(self::TABLE);
            /** @var array<string, string> */
            $data = $connection->fetchAssociative(
                <<<EOS
                SELECT
                    *
                FROM
                    $table
                WHERE
                    name = ?
                EOS,
                [$account->getName()],
            );
        } catch (Throwable $error) {
            $this->logger->error('Error checking for existing account', [
                'account_id' => $account->getId(),
                'account_name' => $account->getName(),
                'error' => $error,
            ]);
            throw Exception::databaseUnknownError($error);
        }

        if (!empty($data)) {
            $existing = Account::fromArray($data);
            if ($account->getId() !== $existing->getId()) {
                throw Exception::accountExists($account->getName());
            }
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
                'account_name' => $account->getName(),
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
}

