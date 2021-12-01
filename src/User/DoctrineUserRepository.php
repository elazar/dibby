<?php

namespace Elazar\Dibby\User;

use Elazar\Dibby\{
    Database\DoctrineConnectionFactory,
    Exception,
};
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Throwable;

class DoctrineUserRepository implements UserRepository
{
    private const TABLE = 'user';

    public function __construct(
        private DoctrineConnectionFactory $connectionFactory,
        private LoggerInterface $logger,
    ) { }

    public function persistUser(User $user): User
    {
        try {
            $existing = $this->getUserByEmail($user->getEmail());
            if ($existing->getId() !== $user->getId()) {
                throw Exception::userExists($user->getEmail());
            }
        } catch (Exception $error) {
            if ($error->getCode() !== Exception::CODE_USER_NOT_FOUND) {
                throw $error;
            }
        }

        if ($user->getId() === null) {
            $method = 'insert';
            $user = $user->withId(Uuid::uuid4());
            $args = [];
        } else {
            $method = 'update';
            $args = [['id' => $user->getId()]];
        }

        try {
            $connection = $this->connectionFactory->getWriteConnection();
            $table = $connection->quoteIdentifier(self::TABLE);
            $data = $user->toArray();
            $connection->$method($table, $data, ...$args);
            return $user;
        } catch (Throwable $error) {
            $this->logger->error('Error persisting user', [
                'user_id' => $user->getId(),
                'user_email' => $user->getEmail(),
                'error' => $error,
            ]);
            throw $error;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUsers(): array
    {
        $connection = $this->connectionFactory->getReadConnection();
        $table = $connection->quoteIdentifier(self::TABLE);
        try {
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
            /** @var User[] */
            $users = [];
            while (
                /** @var array<string, string> $row */
                $row = $results->fetchAssociative()
            ) {
                $users[] = User::fromArray($row);
            }
            return $users;
        } catch (Throwable $error) {
            $this->logger->warning('Error fetching users', [
                'error' => $error,
            ]);
            throw $error;
        }
    }

    public function getUserById(string $userId): User
    {
        return $this->getUserBy('id', $userId);
    }

    public function getUserByEmail(string $email): User
    {
        return $this->getUserBy('email', $email);
    }

    private function getUserBy(string $field, string $value): User
    {
        try {
            $connection = $this->connectionFactory->getReadConnection();
            $table = $connection->quoteIdentifier(self::TABLE);
            $column = $connection->quoteIdentifier($field);
            /** @var string[]|false */
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
            $this->logger->warning('Error fetching user', [
                'field' => $field,
                'value' => $value,
                'error' => $error,
            ]);
            $data = false;
        }
        if ($data === false) {
            throw Exception::userNotFound($value);
        }
        return User::fromArray($data);
    }

    public function hasUsers(): bool
    {
        $connection = $this->connectionFactory->getReadConnection();
        $table = $connection->quoteIdentifier(self::TABLE);
        $count = $connection->fetchOne(
            <<<EOS
            SELECT
                COUNT(*)
            FROM
                $table
            EOS,
        );
        return $count > 0;
    }
}
