<?php

namespace Elazar\Dibby\Account;

class AccountService
{
    public function __construct(
        AccountRepository $accountRepository,
    ) { }

    public function persistAccount(Account $account): Account
    {
        if (empty($account->getName())) {
            throw Exception::invalidInput('Name is required');
        }
        return $this->accountRepository->persistAccount($account);
    }
}
