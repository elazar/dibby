<?php

namespace Elazar\Dibby;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use League\Route\Http\Exception\HttpExceptionInterface;
use Psr\Http\Message\{
    ResponseFactoryInterface,
    ResponseInterface,
    ServerRequestInterface,
};
use Psr\Http\Server\RequestHandlerInterface;

class Application implements RequestHandlerInterface
{
    public function __construct(
        private ServerRequestInterface $request,
        private RequestHandlerInterface $handler,
        private ResponseFactoryInterface $responseFactory,
        private EmitterInterface $emitter,
    ) { }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->handler->handle($request);
        } catch (HttpExceptionInterface $exception) {
            $response = $this->responseFactory->createResponse($exception->getStatusCode());
            foreach ($exception->getHeaders() as $header => $value) {
                $response = $response->withHeader($header, $value);
            }
        }
        return $response;
    }

    /**
     * @codeCoverageIgnore
     */
    public function run(): void
    {
        $response = $this->handle($this->request);
        $this->emitter->emit($response);
    }
}
