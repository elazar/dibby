<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\User\{
    User,
    UserRepository,
    UserService,
};

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

use Throwable;

class RegisterController
{
    public function __construct(
        private ResponseGenerator $responseGenerator,
        private UserRepository $userRepository,
        private UserService $userService,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->userRepository->hasUsers()) {
            return $this->responseGenerator->redirect('get_login');
        }

        $data = ['title' => 'Register'];
        $status = 200;

        if (strcasecmp($request->getMethod(), 'post') === 0) {
            $body = (array) $request->getParsedBody();
            try {
                $user = $this->userService->persistUser(
                    User::fromArray($body),
                );
                return $this->responseGenerator->logIn($user);
            } catch (Throwable $error) {
                $data['error'] = $error->getMessage();
                $status = 400;
            }
        }

        return $this->responseGenerator->render($request, 'register', $data)
                                       ->withStatus($status);
    }
}
