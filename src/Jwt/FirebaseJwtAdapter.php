<?php

namespace Elazar\Dibby\Jwt;

use Firebase\JWT\{JWT, Key};

class FirebaseJwtAdapter implements JwtAdapter
{
    public function __construct(
        private string $key,
        private string $algorithm = 'HS256',
    ) { }

    public function encode(object|array $payload): string
    {
        return JWT::encode($payload, $this->key, $this->algorithm);
    }

    public function decode(string $jwt): object
    {
        return (object) JWT::decode($jwt, new Key($this->key, $this->algorithm));
    }
}
