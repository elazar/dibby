<?php

namespace Elazar\Dibby;

use Elazar\Dibby\Controller\{
    GetIndexController,
};
use League\Route\Router;

class RouteConfiguration
{
    /**
     * @var array<array<string>>
     */
    private array $routes;

    /**
     * @var array<string, string>
     */
    private ?array $namePathMap = null;

    public function __construct()
    {
        $this->routes = [
            ['GET', '/', GetIndexController::class, 'get_index'],
            /* ['GET', '/login', GetLoginController::class, 'get_login'], */
            /* ['POST', '/login', PostLoginController::class, 'post_login'], */
            /* ['GET', '/dashboard', GetDashboardController::class, 'get_dashboard'], */
        ];
    }

    public function getPath(string $name): string
    {
        if ($this->namePathMap === null) {
            foreach ($this->routes as $route) {
                [ $_, $path, $_, $name ] = $route;
                $this->namePathMap[$name] = $path;
            }
        }

        if (!isset($this->namePathMap[$name])) {
            throw Exception::routeNotFound($name);
        }

        return $this->namePathMap[$name];
    }

    public function apply(Router $router): Router
    {
        $router = clone $router;
        foreach ($this->routes as $route) {
            [$method, $path, $controller, $name] = $route;
            $router->map($method, $path, $controller)->setName($name);
        }
        return $router;
    }
}
