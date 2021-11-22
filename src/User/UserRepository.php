<?php

namespace Elazar\Dibby\User;

interface UserRepository
{
    /**
     * Should call withId() on $user and pass it a value for a unique
     * identifier for the user that is appropriate for storage in this
     * repository implentation.
     *
     * Should persist the return value of $user->getPasswordHash() but
     * NOT $user->getPassword(); the latter is only intended to temporarily
     * contain an original password until a hash for it can be computed.
     *
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

    public function hasUsers(): bool;
}
