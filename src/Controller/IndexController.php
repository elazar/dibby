<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\Session;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class IndexController
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private array $env,
        private Session $session,
        private string $configurePath,
        private string $loginPath,
        private string $dashboardPath,
    ) { }

    public function __invoke(
        ServerRequestInterface $request,
    ): ResponseInterface {
        if (empty($this->env)) {
            return $this->redirect($this->configurePath);
        }
        if (!$this->session->isAuthenticated()) {
            return $this->redirect($this->loginPath);
        }
        return $this->redirect($this->dashboardPath);
    }

    private function redirect(string $path): ResponseInterface
    {
        return $this->responseFactory
                    ->createResponse(302)
                    ->withHeader('Location', $path);
    }
}
