<?php

namespace Elazar\Dibby\Account;

interface AccountRepository
{
    /**
     * Should call withId() on $account and pass it a value for a unique
     * identifier for the user that is appropriate for storage in this
     * repository implentation.
     *
     * Should enforce uniqueness of account names when persisting accounts.
     *
     * @throws \Elazar\Dibby\Exception if account cannot be persisted
     */
    public function persistAccount(Account $account): Account;

    /**
     * @return Account[]
     */
    public function getAccounts(): array;

    public function getAccountById(string $id): Account;

    public function getAccountByName(string $name): Account;
}
