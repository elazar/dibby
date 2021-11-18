<?php

namespace Elazar\Dibby;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Logging\SQLLogger;
use Elazar\Dibby\Configuration\Configuration;
use Elazar\Dibby\Configuration\ConfigurationFactory;
use Elazar\Dibby\Configuration\EnvConfigurationFactory;
use Elazar\Dibby\Database\DatabaseConnectionFactory;
use Elazar\Dibby\Database\DoctrineConnectionFactory;
use Elazar\Dibby\Jwt\JwtMiddleware;
use Elazar\Dibby\Jwt\JwtRequestTransformer;
use Elazar\Dibby\Jwt\UserJwtRequestTransformer;
use Elazar\Dibby\User\DoctrineUserRepository;
use Elazar\Dibby\User\UserRepositoryInterface;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Plates\Engine as PlatesEngine;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Strategy\StrategyInterface;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class PimpleServiceProvider implements ServiceProviderInterface
{
    /**
     * @return void
     */
    public function register(Container $pimple)
    {
        // PSR-7 implementation
        $pimple[Psr17Factory::class] = fn() => new Psr17Factory;
        $pimple[ResponseFactoryInterface::class] = fn($c) => $c[Psr17Factory::class];
        $pimple[ServerRequestFactoryInterface::class] = fn($c) => $c[Psr17Factory::class];
        $pimple[UriFactoryInterface::class] = fn($c) => $c[Psr17Factory::class];
        $pimple[UploadedFileFactoryInterface::class] = fn($c) => $c[Psr17Factory::class];
        $pimple[StreamFactoryInterface::class] = fn($c) => $c[Psr17Factory::class];

        // Request creator
        $pimple[ServerRequestCreator::class] = fn($c) => new ServerRequestCreator(
            $c[ServerRequestFactoryInterface::class],
            $c[UriFactoryInterface::class],
            $c[UploadedFileFactoryInterface::class],
            $c[StreamFactoryInterface::class],
        );
        $pimple[ServerRequestCreatorInterface::class] = fn($c) => $c[ServerRequestCreator::class];
        $pimple[ServerRequestInterface::class] = fn($c) => $c[ServerRequestCreatorInterface::class]->fromGlobals();

        // PSR-15 middleware implementations
        $pimple[DateTimeImmutable::class] = new DateTimeImmutable;
        $pimple[JwtRequestTransformer::class] = fn($c) => $c[UserJwtRequestTransformer::class];
        $pimple[JwtMiddleware::class] = fn($c) => new JwtMiddleware(
            $c[LoggerInterface::class],
            $c[JwtRequestTransformer::class],
            $c[DateTimeImmutable::class],
            $c[Configuration::class]->getSessionKey(),
            $c[Configuration::class]->getSessionTimeToLive(),
        );

        // PSR-15 request handler implementation
        $pimple[ApplicationStrategy::class] = fn($c) => (new ApplicationStrategy)
            ->setContainer(new PsrContainer($c));
        $pimple[StrategyInterface::class] = fn($c) => $c[ApplicationStrategy::class];
        $pimple[Router::class] = function ($c) {
            $router = new Router;
            $router->setStrategy($c[StrategyInterface::class]);
            $router->middleware($c[JwtMiddleware::class]);
            return $this->withRoutes($router);
        };
        $pimple[RequestHandlerInterface::class] = fn($c) => $c[Router::class];

        // Response emitter
        $pimple[EmitterInterface::class] = fn($c) => $c[SapiEmitter::class];
        $pimple[SapiEmitter::class] = fn() => new SapiEmitter;

        // Application and supporting classes
        $pimple[Application::class] = fn($c) => new Application(
            $c[ServerRequestInterface::class],
            $c[RequestHandlerInterface::class],
            $c[EmitterInterface::class],
        );
        $pimple[RoutePathMap::class] = fn($c) => new RoutePathMap(
            $c[Router::class],
        );

        // Template engine
        $pimple[PlatesRouteExtension::class] = fn($c) => new PlatesRouteExtension(
            $c[RoutePathMap::class],
        );
        $pimple[PlatesEngine::class] = function ($c) {
            $engine = new PlatesEngine(__DIR__ . '/../templates');
            $engine->loadExtension($c[PlatesRouteExtension::class]);
            return $engine;
        };

        // Logger
        $pimple[Logger::class] = function ($c) {
            $handler = new StreamHandler('php://stderr');
            $handler->setFormatter(new NormalizerFormatter);
            $logger = new Logger('dibby');
            $logger->pushHandler($handler);
            return $logger;
        };
        $pimple[LoggerInterface::class] = fn($c) => $c[Logger::class];

        // Configuration
        $pimple[EnvConfigurationFactory::class] = new EnvConfigurationFactory;
        $pimple[ConfigurationFactory::class] = $pimple[EnvConfigurationFactory::class];
        $pimple[Configuration::class] = fn($c) => $c[ConfigurationFactory::class]->getConfiguration();

        // Doctrine
        $pimple[DebugStack::class] = fn($c) => new DebugStack;
        $pimple[SQLLogger::class] = fn($c) => $c[DebugStack::class];
        $pimple[DoctrineConnectionFactory::class] = fn($c) => new DoctrineConnectionFactory(
            $c[Configuration::class]->getDatabaseReadConfiguration(),
            $c[Configuration::class]->getDatabaseWriteConfiguration(),
            $c[SQLLogger::class],
        );
        $pimple[DatabaseConnectionFactory::class] = fn($c) => $c[DoctrineConnectionFactory::class];

        // Users
        $pimple[DoctrineUserRepository::class] = fn($c) => new DoctrineUserRepository(
            $c[DoctrineConnectionFactory::class],
        );
        $pimple[UserRepositoryInterface::class] = fn($c) => $c[DoctrineUserRepository::class];

        // Controllers
        $pimple[GetIndexController::class] = fn($c) => new GetIndexController(
            $c[ResponseFactoryInterface::class],
            $c[RoutePathMap::class],
        );
    }

    private function withRoutes(Router $router): Router
    {
        $routes = [
            /* ['GET', '/', GetIndexController::class, 'get_index'], */
            /* ['GET', '/login', GetLoginController::class, 'get_login'], */
            /* ['POST', '/login', PostLoginController::class, 'post_login'], */
            /* ['GET', '/dashboard', GetDashboardController::class, 'get_dashboard'], */
        ];

        foreach ($routes as $route) {
            [$method, $path, $controller, $name] = $route;
            $router->map($method, $path, $controller)->setName($name);
        }

        return $router;
    }
}
