<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\Account\{
    Account,
    AccountRepository,
};
use Elazar\Dibby\Transaction\TransactionRepository;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class AccountsReportsController
{
    public function __construct(
        private AccountRepository $accountRepository,
        private TransactionRepository $transactionRepository,
        private ResponseGenerator $responseGenerator,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        if ($user === null) {
            return $this->responseGenerator->redirect('get_login');
        }

        $accounts = $this->accountRepository->getAccounts();
        $creditAccounts = array_filter($accounts, fn(Account $account): bool => $account->getCreditLimit() !== null);
        $accountIds = array_map(fn(Account $account): string => $account->getId(), $accounts);
        $balances = array_combine($accountIds, array_map(
            fn(Account $account): float => $this->transactionRepository->getAccountBalance($account->getId()),
            $accounts,
        ));

        $data = [
            'accounts' => $accounts,
            'creditAccounts' => $creditAccounts,
            'balances' => $balances,
        ];
        return $this->responseGenerator->render($request, 'accounts-reports', $data);
    }
}
