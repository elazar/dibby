<?php

namespace Elazar\Dibby\Template;

use Elazar\Dibby\RouteConfiguration;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PlatesRouteExtension implements ExtensionInterface
{
    public function __construct(
        private RouteConfiguration $routes,
    ) { }

    /**
     * @return void
     */
    public function register(Engine $engine)
    {
        $engine->registerFunction(
            'route',
            fn(string $name, ?array $parameters = null): string => $this->routes->getPath($name, $parameters),
        );
    }
}
