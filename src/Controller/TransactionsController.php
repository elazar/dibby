<?php

namespace Elazar\Dibby\Controller;

use DateTimeImmutable;
use Elazar\Dibby\Transaction\{
    TransactionCriteria,
    TransactionRepository,
    TransactionService,
};
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class TransactionsController
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private TransactionService $transactionService,
        private ResponseGenerator $responseGenerator,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        if ($user === null) {
            return $this->responseGenerator->redirect('get_login');
        }

        $params = $request->getQueryParams();
        if (empty($params)) {
            $params['date_start'] = (new DateTimeImmutable('30 days ago'))->format('Y-m-d');
        }
        $criteria = TransactionCriteria::fromArray($params);
        $transactions = $this->transactionRepository->getTransactions($criteria);
        $summary = $this->transactionService->getSummary($transactions);

        $data = [
            'transactions' => $transactions,
            'summary' => $summary,
        ];
        return $this->responseGenerator->render($request, 'transactions', $data);
    }
}
