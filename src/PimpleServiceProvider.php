<?php

namespace Elazar\Dibby;

use DateTimeImmutable;

use Doctrine\DBAL\{
    Connection,
    Logging\DebugStack,
    Logging\SQLLogger,
};

use Elazar\Dibby\Configuration\{
    Configuration,
    ConfigurationFactory,
    EnvConfigurationFactory,
    PhpConfigurationFactory,
};

use Elazar\Dibby\Controller\{
    GetIndexController,
};

use Elazar\Dibby\Database\{
    DatabaseConnectionFactory,
    DoctrineConnectionFactory,
};

use Elazar\Dibby\Jwt\{
    JwtMiddleware,
    JwtRequestTransformer,
    UserJwtRequestTransformer,
};

use Elazar\Dibby\Template\{
    PlatesRouteExtension,
    PlatesTemplateEngine,
    TemplateEngine,
};

use Elazar\Dibby\User\{
    DoctrineUserRepository,
    UserRepositoryInterface,
};

use Laminas\HttpHandlerRunner\Emitter\{
    EmitterInterface,
    SapiEmitter,
};

use League\Flysystem\{
    Filesystem,
    FilesystemOperator,
    Local\LocalFilesystemAdapter,
};

use League\Plates\Engine as PlatesEngine;

use League\Route\{
    Router,
    Strategy\ApplicationStrategy,
    Strategy\StrategyInterface,
};

use Monolog\{
    Formatter\NormalizerFormatter,
    Handler\StreamHandler,
    Logger,
};

use Nyholm\Psr7\Factory\Psr17Factory;

use Nyholm\Psr7Server\{
    ServerRequestCreator,
    ServerRequestCreatorInterface,
};

use Pimple\{
    Container,
    Psr11\Container as PsrContainer,
    ServiceProviderInterface,
};

use Psr\Http\Message\{
    ResponseFactoryInterface,
    ServerRequestInterface,
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UploadedFileFactoryInterface,
    UriFactoryInterface,
};

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
            return $c[RouteConfiguration::class]->apply($router);
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
        $pimple[RouteConfiguration::class] = fn() => new RouteConfiguration;

        // Template engine
        $pimple[PlatesRouteExtension::class] = fn($c) => new PlatesRouteExtension(
            $c[RouteConfiguration::class],
        );
        $pimple[PlatesEngine::class] = function ($c) {
            $engine = new PlatesEngine(__DIR__ . '/../templates');
            $engine->loadExtension($c[PlatesRouteExtension::class]);
            return $engine;
        };
        $pimple[PlatesTemplateEngine::class] = fn($c) => new PlatesTemplateEngine(
            $c[PlatesEngine::class],
        );
        $pimple[TemplateEngine::class] = fn($c) => $c[PlatesTemplateEngine::class];

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
        $pimple[PhpConfigurationFactory::class] = new PhpConfigurationFactory;
        $pimple[ConfigurationFactory::class] = $pimple[PhpConfigurationFactory::class];
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
            $c[RouteConfiguration::class],
        );
    }
}
