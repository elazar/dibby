<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\User\UserService;

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class LoginController
{
    public function __construct(
        private ResponseGenerator $responseGenerator,
        private UserService $userService,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];
        $status = 200;

        if (strcasecmp($request->getMethod(), 'post') === 0) {
            $body = (array) $request->getParsedBody();
            $user = $this->userService->authenticateUser(
                $body['email'] ?? '',
                $body['password'] ?? '',
            );
            if ($user !== null) {
                return $this->responseGenerator->logIn($user);
            }
            $data['error'] = 'Unrecognized e-mail or password.';
            $status = 403;
        }

        return $this->responseGenerator->render(
            request: $request,
            template: 'login',
            data: $data,
            status: $status,
        );
    }
}
