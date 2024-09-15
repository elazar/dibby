<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\Account\AccountRepository;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class AccountsController
{
    public function __construct(
        private AccountRepository $accountRepository,
        private ResponseGenerator $responseGenerator,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        if ($user === null) {
            return $this->responseGenerator->redirect('get_login');
        }

        $data = [
            'accounts' => $this->accountRepository->getAccounts(),
        ];
        return $this->responseGenerator->render($request, 'accounts', $data);
    }
}
