<?php

namespace Elazar\Dibby\Jwt;

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

class JwtMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private JwtRequestTransformer $jwtRequestTransformer,
        private JwtAdapter $jwtAdapter,
        private JwtResponseTransformer $jwtResponseTransformer,
        private string $sessionCookie,
    ) { }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $token = $this->getToken($request);
        if (empty($token)) {
            return $handler->handle($request);
        }

        $decoded = $this->decodeToken($token);
        if ($decoded === null) {
            return $handler->handle($request);
        }

        $request = $this->jwtRequestTransformer->transform($request, $decoded);
        $response = $handler->handle($request);

        return $this->jwtResponseTransformer->transform($response, $token);
    }

    private function getToken(ServerRequestInterface $request): ?string
    {
        return $request->getCookieParams()[$this->sessionCookie] ?? null;
    }

    private function decodeToken(string $token): ?object
    {
        try {
            return $this->jwtAdapter->decode($token);
        } catch (Throwable $error) {
            $this->logger->warning('JWT token decoding failed', [
                'token' => $token,
                'error' => $error,
            ]);
        }
        return null;
    }
}
