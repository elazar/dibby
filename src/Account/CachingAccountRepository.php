<?php

namespace Elazar\Dibby\Account;

class CachingAccountRepository implements AccountRepository
{
    /**
     * @var array<string, Account>
     */
    private array $cacheById = [];

    /**
     * @var array<string, Account>
     */
    private array $cacheByName = [];

    public function __construct(
        private AccountRepository $accountRepository,
    ) { }

    public function persistAccount(Account $account): Account
    {
        $account = $this->accountRepository->persistAccount($account);
        $this->cacheAccount($account);
        return $account;
    }

    public function getAccounts(): array
    {
        $accounts = $this->accountRepository->getAccounts();
        foreach ($accounts as $account) {
            $this->cacheAccount($account);
        }
        return $accounts;
    }

    public function getAccountById(string $id): Account
    {
        if (!isset($this->cacheById[$id])) {
            $account = $this->accountRepository->getAccountById($id);
            $this->cacheAccount($account);
        }
        return $this->cacheById[$id];
    }

    public function getAccountByName(string $name): Account
    {
        if (!isset($this->cacheByName[$name])) {
            $account = $this->accountRepository->getAccountByName($name);
            $this->cacheAccount($account);
        }
        return $this->cacheByName[$name];
    }

    private function cacheAccount(Account $account): void
    {
        $this->cacheById[$account->getId()] = $account;
        $this->cacheByName[$account->getName()] = $account;
    }
}
