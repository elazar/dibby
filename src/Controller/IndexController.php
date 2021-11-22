<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\User\UserRepository;

use Psr\Http\Message\{
    ResponseFactoryInterface,
    ResponseInterface,
    ServerRequestInterface,
};

class IndexController
{
    public function __construct(
        private ResponseGenerator $responseGenerator,
        private UserRepository $userRepository,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $route = match(true) {
            !$this->userRepository->hasUsers() => 'get_register',
            $request->getAttribute('user') !== null => 'get_dashboard',
            default => 'get_login',
        };
        return $this->responseGenerator->redirect($route);
    }
}
