<?php

namespace Elazar\Dibby\User;

interface PasswordHasher
{
    public function getHash(string $password): string;

    public function verifyHash(string $password, string $hash): bool;
}
