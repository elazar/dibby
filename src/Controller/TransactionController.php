<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\{
    Account\AccountRepository,
    Transaction\Transaction,
    Transaction\TransactionService,
};

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class TransactionController
{
    public function __construct(
        private ResponseGenerator $responseGenerator,
        private AccountRepository $accountRepository,
        private TransactionService $transactionService,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ?\Elazar\Dibby\User\User $user */
        $user = $request->getAttribute('user');
        if ($user === null) {
            return $this->responseGenerator->redirect('get_login');
        }

        if (strcasecmp($request->getMethod(), 'post') === 0) {
            $body = (array) $request->getParsedBody();
            $transaction = $this->transactionService->fromArray($body);
            $transaction = $this->transactionService->persistTransaction($transaction);
            return $this->responseGenerator->redirect('get_transactions');
        }

        $data = [
            'user' => $user,
            'accounts' => $this->accountRepository->getAccounts(),
        ];
        return $this->responseGenerator->render($request, 'transaction', $data);
    }
}
