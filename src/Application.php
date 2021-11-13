<?php

namespace Elazar\Dibby;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Application
{
    public function __construct(
        private ServerRequestInterface $request,
        private RequestHandlerInterface $handler,
        private EmitterInterface $emitter,
    ) { }

    public function run(): void
    {
        $response = $this->handler->dispatch($this->request);
        $this->emitter->emit($response);
    }
}
