<?php

namespace Elazar\Dibby\Jwt;

interface JwtAdapter
{
    /**
     * @param object|array<string, string> $payload
     */
    public function encode(object|array $payload): string;

    public function decode(string $jwt): object;
}
