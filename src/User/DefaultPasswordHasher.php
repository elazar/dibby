<?php

namespace Elazar\Dibby\User;

class DefaultPasswordHasher implements PasswordHasher
{
    public function __construct(
        private int $cost = 10,
    ) { }

    public function getHash(string $password): string
    {
        return password_hash(
            $password,
            null,
            ['cost' => $this->cost],
        );
    }

    public function verifyHash(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
