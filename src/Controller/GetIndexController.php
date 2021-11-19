<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\RouteConfiguration;

use Psr\Http\Message\{
    ResponseFactoryInterface,
    ResponseInterface,
    ServerRequestInterface,
};

class GetIndexController
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private RouteConfiguration $routes,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $redirect = fn(string $name): ResponseInterface => $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', $this->routes->getPath($name));

        $user = $request->getAttribute('user');
        if ($user === null) {
            return $redirect('login');
        }
        return $redirect('dashboard');
    }
}
