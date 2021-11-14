<?php

namespace Elazar\Dibby;

use League\Route\Router;

class RoutePathMap
{
    public function __construct(
        private Router $router,
    ) { }

    public function getPath(string $name): string
    {
        return $this->router
                    ->getNamedRoute($name)
                    ->getPath();
    }
}
