<?php

namespace Elazar\Dibby;

use Elazar\Dibby\Controller\{
    DashboardController,
    IndexController,
    LoginController,
    PasswordController,
    RegisterController,
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
            ['GET', '/', IndexController::class, 'get_index'],
            ['GET', '/login', LoginController::class, 'get_login'],
            ['POST', '/login', LoginController::class, 'post_login'],
            ['GET', '/password', PasswordController::class, 'get_password'],
            ['POST', '/password', PasswordController::class, 'post_password'],
            ['GET', '/register', RegisterController::class, 'get_register'],
            ['POST', '/register', RegisterController::class, 'post_register'],
            ['GET', '/dashboard', DashboardController::class, 'get_dashboard'],
        ];
    }

    public function getPath(string $name): string
    {
        if ($this->namePathMap === null) {
            foreach ($this->routes as $route) {
                [ $_, $routePath, $_, $routeName ] = $route;
                $this->namePathMap[$routeName] = $routePath;
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
