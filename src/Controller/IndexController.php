<?php

namespace Elazar\Dibby\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class IndexController
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
    ) { }

    public function __invoke(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $response = $this->responseFactory
                         ->createResponse(200)
                         ->withHeader('Content-Type', 'text/plain');
        $response->getBody()->write('Hello World!');
        return $response;
    }
}
