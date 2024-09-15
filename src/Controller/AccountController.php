<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\{
    Account\Account,
    Account\AccountRepository,
    Account\AccountService,
};
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};
use Throwable;

class AccountController
{
    public function __construct(
        private AccountRepository $accountRepository,
        private AccountService $accountService,
        private ResponseGenerator $responseGenerator,
    ) { }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $request->getAttribute('user');
        if ($user === null) {
            return $this->responseGenerator->redirect('get_login');
        }

        $data = [];
        if (strcasecmp($request->getMethod(), 'post') === 0) {
            $body = (array) $request->getParsedBody();
            try {
                $account = $this->accountService->persistAccount(
                    Account::fromArray($body),
                );
                return $this->responseGenerator->redirect('get_accounts');
            } catch (Throwable $error) {
                $data += $body;
                $data['error'] = $error->getMessage();
                $status = 400;
            }
        } elseif (isset($args['accountId'])) {
            $account = $this->accountRepository->getAccountById($args['accountId']);
            $data = [
                'id' => $account->getId(),
                'name' => $account->getName(),
                'creditLimit' => $account->getCreditLimit(),
            ];
        }
        return $this->responseGenerator->render($request, 'account', $data);
    }
}
