<?php

namespace Elazar\Dibby\Jwt;

interface JwtAdapter
{
    public function encode(object|array $payload): string;

    public function decode(string $jwt): object;
}
