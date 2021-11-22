<?php

namespace Elazar\Dibby\Controller;

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class PasswordController
{
    public function __construct(
        private ResponseGenerator $responseGenerator,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseGenerator->render(
            $request,
            'password',
            ['title' => 'Forgot Password'],
        );
    }
}
