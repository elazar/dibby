<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\{
    Account\AccountRepository,
    Transaction\Transaction,
    Transaction\TransactionRepository,
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
        private TransactionRepository $transactionRepository,
    ) { }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var ?\Elazar\Dibby\User\User $user */
        $user = $request->getAttribute('user');
        if ($user === null) {
            return $this->responseGenerator->redirect('get_login');
        }

        $data = [
            'accounts' => $this->accountRepository->getAccounts(),
        ];

        if (strcasecmp($request->getMethod(), 'post') === 0) {
            $body = (array) $request->getParsedBody();
            $transaction = $this->transactionService->fromArray($body);
            $this->transactionService->persistTransaction($transaction);
            return $this->responseGenerator->redirect('get_transactions');
        } elseif (isset($args['transactionId'])) {
            $transaction = $this->transactionRepository->getTransactionById($args['transactionId']);
            $data += [
                'id' => $transaction->getId(),
                'amount' => $transaction->getAmount(),
                'debitAccount' => $transaction->getDebitAccount()->getName(),
                'creditAccount' => $transaction->getCreditAccount()->getName(),
                'description' => $transaction->getDescription(),
                'date' => $transaction->getDate()->format('Y-m-d'),
            ];
        }

        return $this->responseGenerator->render($request, 'transaction', $data);
    }
}
