<?php

namespace Elazar\Dibby;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Logging\SQLLogger;
use Elazar\Dibby\Controller\ConfigureController;
use Elazar\Dibby\Controller\IndexController;
/* use Elazar\Dibby\Controller\InstallController; */
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Lcobucci\JWT\Signer\Key\InMemory as Key;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Plates\Engine as PlatesEngine;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Strategy\StrategyInterface;
use M1\Env\Parser as EnvParser;
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
use Psr\SimpleCache\CacheInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class PimpleServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // PSR-16 cache implementations
        $pimple['cache.apcu'] = fn($c): CacheInterface => new Psr16Cache(
            extension_loaded('apcu') ? new ApcuAdapter : new ArrayAdapter,
        );

        // Filesystem abstraction
        $pimple['filesystem.local'] = fn($c) => new Filesystem(
            new LocalFilesystemAdapter(__DIR__ . '/..'),
        );

        // Environmental variables
        $pimple[EnvFactory::class] = fn($c) => new EnvFactory(
            $pimple['cache.apcu'],
            'env',
            $pimple['filesystem.local'],
            '.env',
        );
        $pimple['env'] = fn($c): array => $c[EnvFactory::class]->get();

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

        // PSR-15 request handler implementation
        $pimple[ApplicationStrategy::class] = fn($c) => (new ApplicationStrategy)
            ->setContainer(new PsrContainer($c));
        $pimple[StrategyInterface::class] = fn($c) => $c[ApplicationStrategy::class];
        $pimple[Router::class] = function ($c) {
            $router = (new Router)->setStrategy($c[StrategyInterface::class]);
            if (isset($c['env']['SESSION_KEY'])) {
                $router->middleware($c[SessionMiddleware::class]);
            }
            return $this->withRoutes($router);
        };
        $pimple[RequestHandlerInterface::class] = fn($c) => $c[Router::class];

        // PSR-15 session middleware
        $pimple[SessionMiddleware::class] = fn($c) => SessionMiddleware::fromSymmetricKeyDefaults(
            Key::base64Encoded($c['env']['SESSION_KEY']),
            $c['env']['SESSION_TTL'],
        );
        $pimple[Session::class] = fn($c) => new Session($c[ServerRequestInterface::class]);

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
        $pimple[PlatesEngine::class] = fn($c) => new PlatesEngine(__DIR__ . '/../templates');

        // Logger
        $pimple[Logger::class] = function ($c) {
            $handler = new StreamHandler('php://stderr');
            $handler->setFormatter(new NormalizerFormatter);
            $logger = new Logger('dibby');
            $logger->pushHandler($handler);
            return $logger;
        };
        $pimple[LoggerInterface::class] = fn($c) => $c[Logger::class];

        // Database
        $pimple[DatabaseConnectionFactory::class] = fn($c) => new DatabaseConnectionFactory($c['env']);
        $pimple[DebugStack::class] = fn($c) => new DebugStack;
        $pimple[SQLLogger::class] = fn($c) => $c[DebugStack::class];
        $pimple[Connection::class] = function ($c) {
            $connection = $c[DatabaseConnectionFactory::class]->getConnection();
            $connection->getConfiguration()->setSQLLogger($c[SQLLogger::class]);
            return $connection;
        };

        // Controllers
        $pimple[IndexController::class] = fn($c) => new IndexController(
            $c[ResponseFactoryInterface::class],
            $c['env'],
            $c[Session::class],
            $c[RoutePathMap::class]->getPath('configure'),
            $c[RoutePathMap::class]->getPath('login'),
            $c[RoutePathMap::class]->getPath('dashboard'),
        );
        $pimple[ConfigureController::class] = fn($c) => new ConfigureController(
            $c[ResponseFactoryInterface::class],
            $c[PlatesEngine::class],
            $c[RoutePathMap::class]->getPath('install'),
        );
        /* $pimple[InstallController::class] = fn($c) => new InstallController( */
        /*     $c[PlatesEngine::class], */
        /* ); */
    }

    private function withRoutes(Router $router): Router
    {
        $routes = [
            ['GET', '/', IndexController::class, 'index'],
            ['GET', '/install', ConfigureController::class, 'configure'],
            ['POST', '/install', InstallController::class, 'install'],
            ['GET', '/login', LoginController::class, 'login'],
            ['POST', '/login', AuthenticateController::class, 'authenticate'],
            ['GET', '/dashboard', DashboardController::class, 'dashboard'],
        ];

        foreach ($routes as $route) {
            [$method, $path, $controller, $name] = $route;
            $router->map($method, $path, $controller)->setName($name);
        }

        return $router;
    }
}
