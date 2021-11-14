<?php

namespace Elazar\Dibby;

use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Session\SessionInterface;

class Session
{
    private const KEY_USER_ID = 'user_id';

    public function __construct(
        private ServerRequestInterface $request,
    ) { }

    private function getSession(): ?SessionInterface
    {
        return $this->request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
    }

    public function isAuthenticated(): bool
    {
        return $this->getSession()?->has(self::KEY_USER_ID) ?? false;
    }
}
