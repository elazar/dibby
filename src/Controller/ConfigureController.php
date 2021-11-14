<?php

namespace Elazar\Dibby\Controller;

use League\Plates\Engine as PlatesEngine;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ConfigureController
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private PlatesEngine $platesEngine,
        private string $installPath,
    ) { }

    public function __invoke(): ResponseInterface
    {
        $response = $this->responseFactory
                         ->createResponse(200)
                         ->withHeader('Content-Type', 'text/html');
        $body = $this->platesEngine->render('configure', [
            'installPath' => $this->installPath,
        ]);
        $response->getBody()->write($body);
        return $response;
    }
}
