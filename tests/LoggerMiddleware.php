<?php

namespace Elazar\Dibby;

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};
use Psr\Http\Server\{
    MiddlewareInterface,
    RequestHandlerInterface,
};
use Psr\Log\LoggerInterface;
use Throwable;

class LoggerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) { }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $this->logger->debug('Received request', ['request' => $request]);
        try {
            $response = $handler->handle($request);
            $this->logger->debug('Returned response', ['response' => $response]);
            return $response;
        } catch (Throwable $error) {
            $this->logger->debug('Encountered error', ['error' => $error]);
            throw $error;
        }
    }
}
