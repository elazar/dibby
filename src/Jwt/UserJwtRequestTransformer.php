<?php

namespace Elazar\Dibby\Jwt;

use Elazar\Dibby\Exception;
use Elazar\Dibby\User\UserRepository;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class UserJwtRequestTransformer implements JwtRequestTransformer
{
    public function __construct(
        private UserRepository $userRepository,
        private LoggerInterface $logger,
    ) { }

    public function transform(
        ServerRequestInterface $request,
        object $jwt,
    ): ServerRequestInterface {
        if (!isset($jwt->sub)) {
            $this->logger->warning('JWT token missing user', [
                'jwt' => $jwt,
            ]);
            return $request;
        }

        try {
            $user = $this->userRepository->getUserById($jwt->sub);
        } catch (Exception $error) {
            $this->logger->warning('Error retrieving JWT user', [
                'user' => $jwt->sub,
                'error' => $error,
            ]);
            return $request;
        }

        return $request->withAttribute('user', $user);
    }
}
