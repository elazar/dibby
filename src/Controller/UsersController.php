<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\User\UserRepository;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class UsersController
{
    public function __construct(
        private ResponseGenerator $responseGenerator,
        private UserRepository $userRepository,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ?\Elazar\Dibby\User\User $user */
        $user = $request->getAttribute('user');
        if ($user === null) {
            return $this->responseGenerator->redirect('get_login');
        }

        $data = [
            'user' => $user,
            'users' => $this->userRepository->getUsers(),
        ];
        return $this->responseGenerator->render($request, 'users', $data);
    }
}
