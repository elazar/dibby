<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\{
    Account\AccountRepository,
    Reconciler\ImporterReconcilerService,
};

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class ReconcileController
{
    public function __construct(
        private ImporterReconcilerService $importerReconcilerService,
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

        if (strcasecmp($request->getMethod(), 'post') === 0) {
            $files = $request->getUploadedFiles();
            if (count($files) > 0) {
                $file = array_shift($files);
                $contents = $file->getStream()->getContents();

                $body = (array) $request->getParsedBody();
                $data['account'] = $body['account'];

                $account = $this->accountRepository->getAccountByName($body['account']);
                $summary = $this->importerReconcilerService->reconcile($contents, $account->getId());
                $data['summary'] = $summary;
            }
        }

        return $this->responseGenerator->render($request, 'reconcile', $data);
    }
}
