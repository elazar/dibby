<?php

namespace Elazar\Dibby\Account;

class AccountService
{
    public function __construct(
        private AccountRepository $accountRepository,
    ) { }

    public function persistAccount(Account $account): Account
    {
        if (empty($account->getName())) {
            throw Exception::invalidInput('Name is required');
        }
        return $this->accountRepository->persistAccount($account);
    }

    public function getOrCreateAccountByName(string $name): Account
    {
        $account = $this->accountRepository->getAccountByName($name);
        if (empty($account->getId())) {
            $account = $this->persistAccount($account);
        }
        return $account;
    }

    public function deleteAccount(string $id): void
    {
        $this->accountRepository->deleteAccount($id);
    }
}
