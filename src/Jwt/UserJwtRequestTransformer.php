<?php

namespace Elazar\Dibby\Jwt;

use Elazar\Dibby\Exception;
use Elazar\Dibby\User\UserRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class UserJwtRequestTransformer implements JwtRequestTransformer
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) { }

    public function transform(
        ServerRequestInterface $request,
        object $jwt,
    ): ServerRequestInterface {
        if (!isset($jwt->user)) {
            $this->logger->debug('JWT token missing user', [
                'jwt' => $jwt,
            ]);
            return $request;
        }

        try {
            $user = $this->userRepository->getUserById($jwt->user);
        } catch (Exception $error) {
            $this->logger->debug('Error retrieving JWT user', [
                'user' => $jwt->user,
                'error' => $error,
            ]);
            return $request;
        }

        return $request->withAttribute('user', $user);
    }
}
