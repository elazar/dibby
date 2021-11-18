<?php

namespace Elazar\Dibby\Jwt;

use DateInterval;
use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class JwtMiddleware implements MiddlewareInterface
{
    private const COOKIE_NAME = 'token';

    public function __construct(
        private LoggerInterface $logger,
        private JwtRequestTransformer $jwtRequestTransformer,
        private DateTimeImmutable $now,
        private string $sessionKey,
        private int $sessionTimeToLive,
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

        $header = 'Set-Cookie';
        $cookie = $this->getCookie($token);
        return $response->hasHeader($header)
            ? $response->withAddedHeader($header, '; ' . $cookie)
            : $response->withHeader($header, $cookie);
    }

    private function getToken(ServerRequestInterface $request): ?string
    {
        return preg_match('/^Bearer (.+)$/i', $request->getHeaderLine('Authorization'), $match) !== 0
            ? $match[1]
            : $request->getCookieParams()[self::COOKIE_NAME] ?? null;
    }

    private function decodeToken(string $token): ?object
    {
        try {
            $key = new Key($this->sessionKey, 'HS256');
            return JWT::decode($token, $key);
        } catch (Throwable $error) {
            $this->logger->debug('JWT token decoding failed', [
                'token' => $token,
                'error' => $error,
            ]);
        }
        return null;
    }

    private function getCookie(string $token): string
    {
        $interval = new DateInterval('P' . $this->sessionTimeToLive . 'S');
        $expires = $this->now->add($interval)->format(DateTimeImmutable::RFC7231);
        return implode('; ', [
            self::COOKIE_NAME . '=' . $token,
            'Expires=' . $expires,
            'Secure',
            'HttpOnly',
        ]);
    }
}
