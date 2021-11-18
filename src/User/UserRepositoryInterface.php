<?php

namespace Elazar\Dibby\User;

interface UserRepositoryInterface
{
    /**
     * @throws \Elazar\Dibby\Exception if user cannot be persisted
     */
    public function persistUser(User $user): User;

    /**
     * @throws \Elazar\Dibby\Exception if user cannot be retrieved
     */
    public function getUserById(string $userId): User;

    /**
     * @throws \Elazar\Dibby\Exception if user cannot be retrieved
     */
    public function getUserByEmail(string $email): User;
}
