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

class AccountSummaryController
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

        $accountId = $args['accountId'];
        $account = $this->accountRepository->getAccountById($accountId);
        $criteria = new TransactionCriteria(
            debitAccountId: $accountId,
            creditAccountId: $accountId,
        );
        $transactions = $this->transactionRepository->getTransactions($criteria);
        $balance = $this->transactionRepository->getAccountBalance($accountId);
        $data = [
            'account' => $account,
            'balance' => $balance,
            'transactions' => $transactions,
        ];
        return $this->responseGenerator->render($request, 'account-summary', $data);
    }
}
