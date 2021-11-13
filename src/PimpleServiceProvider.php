<?php

namespace Elazar\Dibby;

use Elazar\Dibby\Controller\IndexController;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Strategy\StrategyInterface;
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

class PimpleServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // PSR-7 implementation
        $pimple[Psr17Factory::class] = fn() => new Psr17Factory;
        $pimple[ResponseFactoryInterface::class] = fn($c) => $c[Psr17Factory::class];
        $pimple[ServerRequestFactoryInterface::class] = fn($c) => $c[Psr17Factory::class];
        $pimple[UriFactoryInterface::class] = fn($c) => $c[Psr17Factory::class];
        $pimple[UploadedFileFactoryInterface::class] = fn($c) => $c[Psr17Factory::class];
        $pimple[StreamFactoryInterface::class] = fn($c) => $c[Psr17Factory::class];

        // PSR-7 request creator
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
        $pimple[Router::class] = fn($c) => $this->withRoutes(
            (new Router)->setStrategy($c[StrategyInterface::class]),
        );
        $pimple[RequestHandlerInterface::class] = fn($c) => $c[Router::class];

        // Response emitter
        $pimple[EmitterInterface::class] = fn($c) => $c[SapiEmitter::class];
        $pimple[SapiEmitter::class] = fn() => new SapiEmitter;

        // Application
        $pimple[Application::class] = fn($c) => new Application(
            $c[ServerRequestInterface::class],
            $c[RequestHandlerInterface::class],
            $c[EmitterInterface::class],
        );

        // Controllers
        $pimple[IndexController::class] = fn($c) => new IndexController(
            $c[ResponseFactoryInterface::class],
        );
    }

    private function withRoutes(Router $router): Router
    {
        $router->get('/', IndexController::class);
        return $router;
    }
}
