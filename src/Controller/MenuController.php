<?php

namespace Elazar\Dibby\Controller;

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class MenuController
{
    public function __construct(
        private ResponseGenerator $responseGenerator,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ?\Elazar\Dibby\User\User $user */
        $user = $request->getAttribute('user');
        if ($user === null) {
            return $this->responseGenerator->redirect('get_login');
        }

        $data = [
        ];
        return $this->responseGenerator->render($request, 'menu', $data);
    }
}
