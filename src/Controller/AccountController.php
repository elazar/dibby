<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\{
    Account\AccountRepository,
    Transaction\TransactionCriteria,
    Transaction\TransactionRepository,
};
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class AccountController
{
    public function __construct(
        private AccountRepository $accountRepository,
        private TransactionRepository $transactionRepository,
        private ResponseGenerator $responseGenerator,
    ) { }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var ?\Elazar\Dibby\User\User $user */
        $user = $request->getAttribute('user');
        if ($user === null) {
            return $this->responseGenerator->redirect('get_login');
        }

        $account = $this->accountRepository->getAccountById($args['accountId']);
        $criteria = new TransactionCriteria(
            debitAccountId: $account->getId(),
            creditAccountId: $account->getId(),
        );
        $transactions = $this->transactionRepository->getTransactions($criteria);
        $balance = $this->transactionRepository->getAccountBalance($account);
        $data = [
            'account' => $account,
            'balance' => $balance,
            'transactions' => $transactions,
        ];
        return $this->responseGenerator->render($request, 'account', $data);
    }
}
