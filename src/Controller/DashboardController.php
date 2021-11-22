<?php

namespace Elazar\Dibby\Controller;

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class DashboardController
{
    public function __construct(
        private ResponseGenerator $responseGenerator,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getAttribute('user') === null) {
            return $this->responseGenerator->redirect('get_login');
        }

        return $this->responseGenerator->render(
            $request,
            'dashboard',
            ['title' => 'Dashboard'],
        );
    }
}
