<?php

namespace Elazar\Dibby\User;

use Elazar\Dibby\Database\DoctrineConnectionFactory;
use Elazar\Dibby\Exception;
use Ramsey\Uuid\Uuid;

class DoctrineUserRepository implements UserRepositoryInterface
{
    private const TABLE = 'user';

    public function __construct(
        private DoctrineConnectionFactory $connectionFactory,
    ) { }

    public function persistUser(User $user): User
    {
        if ($user->getId() === null) {
            $method = 'insert';
            $user = $user->withId(Uuid::uuid4());
        } else {
            $method = 'update';
        }

        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
        ];
        $connection = $this->connectionFactory->getWriteConnection();
        $connection->$method(self::TABLE, $data);

        return $user;
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
        $connection = $this->connectionFactory->getReadConnection();
        $table = $connection->quoteIdentifier(self::TABLE);
        $column = $connection->quoteIdentifier($field);
        /** @var string[]|false */
        $data = $connection->fetchAssociative(
            <<<EOS
            SELECT
                id,
                email,
                password_hash
            FROM
                $table
            WHERE
                $column = ?
            EOS,
            [$value],
        );
        if ($data === false) {
            throw Exception::userNotFound($value);
        }
        return new User(
            $data['email'],
            $data['password_hash'],
            $data['id'],
        );
    }
}
