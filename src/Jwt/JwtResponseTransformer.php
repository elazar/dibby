<?php

namespace Elazar\Dibby\Jwt;

use DateInterval;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;

class JwtResponseTransformer
{
    public function __construct(
        private DateTimeImmutable $now,
        private string $sessionCookie,
        private string $sessionTimeToLive,
    ) { }

    public function transform(
        ResponseInterface $response,
        string $jwt,
    ): ResponseInterface {
        $header = 'Set-Cookie';
        $cookie = $this->getCookie($jwt);
        return $response->hasHeader($header)
            ? $response->withAddedHeader($header, '; ' . $cookie)
            : $response->withHeader($header, $cookie);
    }

    private function getCookie(string $jwt): string
    {
        $interval = new DateInterval($this->sessionTimeToLive);
        $expires = $this->now->add($interval)->format(DateTimeImmutable::RFC7231);
        return implode('; ', [
            $this->sessionCookie . '=' . $jwt,
            'Expires=' . $expires,
            'Path=/',
            'SameSite=Strict',
            'Secure',
            'HttpOnly',
        ]);
    }
}
