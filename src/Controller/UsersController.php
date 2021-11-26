<?php

namespace Elazar\Dibby\Controller;

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class UsersController
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
            'title' => 'Users',
            'activeRoute' => 'get_users',
            'userName' => $user->getName(),
        ];
        return $this->responseGenerator->render($request, 'users', $data);
    }
}
