<?php

namespace Elazar\Dibby;

use Elazar\Dibby\Controller\{
    AccountsController,
    ActivityController,
    HelpController,
    IndexController,
    LoginController,
    MenuController,
    PasswordController,
    RegisterController,
    ResetController,
    TemplatesController,
    TransactionController,
    TransactionsController,
    UserController,
    UsersController,
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
            ['GET', '/reset', ResetController::class, 'get_reset'],
            ['POST', '/reset', ResetController::class, 'post_reset'],
            ['GET', '/register', RegisterController::class, 'get_register'],
            ['POST', '/register', RegisterController::class, 'post_register'],
            ['GET', '/transactions', TransactionsController::class, 'get_transactions'],
            ['POST', '/transactions', TransactionController::class, 'post_transactions'],
            ['GET', '/transactions/add', TransactionController::class, 'add_transaction'],
            ['GET', '/transactions/{transactionId}', TransactionController::class, 'edit_transaction'],
            ['GET', '/templates', TemplatesController::class, 'get_templates'],
            ['GET', '/accounts', AccountsController::class, 'get_accounts'],
            ['GET', '/activity', ActivityController::class, 'get_activity'],
            ['GET', '/users', UsersController::class, 'get_users'],
            ['GET', '/users/add', UserController::class, 'add_user'],
            ['GET', '/users/{userId}', UserController::class, 'edit_user'],
            ['POST', '/users', UserController::class, 'post_users'],
            ['GET', '/help', HelpController::class, 'get_help'],
            ['GET', '/menu', MenuController::class, 'get_menu'],
        ];
    }

    /**
     * @param array<string, string>|null $parameters
     */
    public function getPath(string $name, ?array $parameters = null): string
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

        $path = $this->namePathMap[$name];
        if (!empty($parameters)) {
            foreach ($parameters as $parameter => $value) {
                $path = str_replace('{' . $parameter . '}', $value, $path);
            }
        }
        return $path;
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
