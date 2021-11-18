<?php

namespace Elazar\Dibby\Jwt;

use Psr\Http\Message\ServerRequestInterface;

interface JwtRequestTransformer
{
    public function transform(
        ServerRequestInterface $request,
        object $jwt,
    ): ServerRequestInterface;
}
