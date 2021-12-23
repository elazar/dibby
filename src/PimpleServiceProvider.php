<?php

namespace Elazar\Dibby;

use DateTimeImmutable;

use Doctrine\DBAL\{
    Configuration as DoctrineConfiguration,
    Connection,
    Logging\Middleware as DoctrineLoggingMiddleware,
};

use Elazar\Dibby\Account\{
    AccountRepository,
    AccountService,
    CachingAccountRepository,
    DoctrineAccountRepository,
};

use Elazar\Dibby\Configuration\{
    Configuration,
    ConfigurationFactory,
    EnvConfigurationFactory,
    PhpFileConfigurationFactory,
};

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
    ResponseGenerator,
    TemplatesController,
    TransactionController,
    TransactionsController,
    UserController,
    UsersController,
};

use Elazar\Dibby\Database\{
    DatabaseConnectionFactory,
    DoctrineConnectionFactory,
    Migrations\CliConfig,
};

use Elazar\Dibby\Email\{
    EmailAdapter,
    EmailService,
    LaminasEmailAdapter,
};

use Elazar\Dibby\Jwt\{
    FirebaseJwtAdapter,
    JwtAdapter,
    JwtMiddleware,
    JwtRequestTransformer,
    JwtResponseTransformer,
    UserJwtRequestTransformer,
};

use Elazar\Dibby\Template\{
    PlatesRouteExtension,
    PlatesTemplateEngine,
    TemplateEngine,
};

use Elazar\Dibby\Transaction\{
    DoctrineTransactionRepository,
    TransactionRepository,
    TransactionService,
};

use Elazar\Dibby\User\{
    DefaultPasswordGenerator,
    DefaultPasswordHasher,
    DefaultResetTokenGenerator,
    DoctrineUserRepository,
    PasswordGenerator,
    PasswordHasher,
    ResetTokenGenerator,
    UserRepository,
    UserService,
};

use Laminas\HttpHandlerRunner\Emitter\{
    EmitterInterface,
    SapiEmitter,
};

use Laminas\Mail\Transport\{
    Smtp,
    SmtpOptions,
    TransportInterface,
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
        $pimple[UserJwtRequestTransformer::class] = fn($c) => new UserJwtRequestTransformer(
            $c[UserRepository::class],
            $c[LoggerInterface::class],
        );
        $pimple[JwtRequestTransformer::class] = fn($c) => $c[UserJwtRequestTransformer::class];
        $pimple[FirebaseJwtAdapter::class] = fn($c) => new FirebaseJwtAdapter(
            $c[Configuration::class]->getSessionKey(),
        );
        $pimple[JwtAdapter::class] = fn($c) => $c[FirebaseJwtAdapter::class];
        $pimple[JwtMiddleware::class] = fn($c) => new JwtMiddleware(
            $c[LoggerInterface::class],
            $c[JwtRequestTransformer::class],
            $c[JwtAdapter::class],
            $c[JwtResponseTransformer::class],
            $c[Configuration::class]->getSessionCookie(),
        );
        $pimple[JwtResponseTransformer::class] = fn($c) => new JwtResponseTransformer(
            $c[DateTimeImmutable::class],
            $c[Configuration::class]->getSessionCookie(),
            $c[Configuration::class]->getSessionTimeToLive(),
            $c[Configuration::class]->getSessionSecure(),
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
            $c[ResponseFactoryInterface::class],
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
            /* $handler->setFormatter(new NormalizerFormatter); */
            $logger = new Logger('dibby');
            $logger->pushHandler($handler);
            return $logger;
        };
        $pimple[LoggerInterface::class] = fn($c) => $c[Logger::class];

        // Configuration
        $pimple[EnvConfigurationFactory::class] = new EnvConfigurationFactory;
        $pimple[PhpFileConfigurationFactory::class] = new PhpFileConfigurationFactory;
        $pimple[ConfigurationFactory::class] = $pimple[PhpFileConfigurationFactory::class];
        $pimple[Configuration::class] = fn($c) => $c[ConfigurationFactory::class]->getConfiguration();

        // Doctrine
        $pimple[DoctrineLoggingMiddleware::class] = fn($c) => new DoctrineLoggingMiddleware(
            $c[LoggerInterface::class],
        );
        $pimple[DoctrineConfiguration::class] = function ($c) {
            $configuration = new DoctrineConfiguration;
            $configuration->setMiddlewares([
                $c[DoctrineLoggingMiddleware::class],
            ]);
            return $configuration;
        };
        $pimple[DoctrineConnectionFactory::class] = fn($c) => new DoctrineConnectionFactory(
            $c[Configuration::class]->getDatabaseReadConfiguration(),
            $c[Configuration::class]->getDatabaseWriteConfiguration(),
            $c[DoctrineConfiguration::class],
        );
        $pimple[DatabaseConnectionFactory::class] = fn($c) => $c[DoctrineConnectionFactory::class];
        $pimple[CliConfig::class] = fn($c) => new CliConfig($c[DoctrineConnectionFactory::class]);

        // E-mail
        $pimple[SmtpOptions::class] = fn($c) => new SmtpOptions([
            'host' => $c[Configuration::class]->getSmtpHost(),
            'port' => (int) $c[Configuration::class]->getSmtpPort(),
        ]);
        $pimple[Smtp::class] = fn($c) => new Smtp($c[SmtpOptions::class]);
        $pimple[TransportInterface::class] = fn($c) => $c[Smtp::class];
        $pimple[LaminasEmailAdapter::class] = fn($c) => new LaminasEmailAdapter(
            $c[TransportInterface::class],
            $c[LoggerInterface::class],
        );
        $pimple[EmailAdapter::class] = fn($c) => $c[LaminasEmailAdapter::class];
        $pimple[EmailService::class] = fn($c) => new EmailService(
            $c[EmailAdapter::class],
            $c[TemplateEngine::class],
            $c[RouteConfiguration::class],
            $c[Configuration::class]->getFromEmail(),
            $c[Configuration::class]->getBaseUrl(),
        );

        // Users
        $pimple[DefaultPasswordGenerator::class] = fn() => new DefaultPasswordGenerator;
        $pimple[PasswordGenerator::class] = fn($c) => $c[DefaultPasswordGenerator::class];
        $pimple[DefaultPasswordHasher::class] = fn() => new DefaultPasswordHasher;
        $pimple[PasswordHasher::class] = fn($c) => $c[DefaultPasswordHasher::class];
        $pimple[DefaultResetTokenGenerator::class] = fn() => new DefaultResetTokenGenerator;
        $pimple[ResetTokenGenerator::class] = fn($c) => $c[DefaultResetTokenGenerator::class];
        $pimple[DoctrineUserRepository::class] = fn($c) => new DoctrineUserRepository(
            $c[DoctrineConnectionFactory::class],
            $c[LoggerInterface::class],
        );
        $pimple[UserRepository::class] = fn($c) => $c[DoctrineUserRepository::class];
        $pimple[UserService::class] = fn($c) => new UserService(
            $c[UserRepository::class],
            $c[PasswordGenerator::class],
            $c[PasswordHasher::class],
            $c[ResetTokenGenerator::class],
            $c[EmailService::class],
            $c[LoggerInterface::class],
            $c[DateTimeImmutable::class],
            $c[Configuration::class]->getResetTokenTimeToLive(),
        );

        // Accounts
        $pimple[DoctrineAccountRepository::class] = fn($c) => new DoctrineAccountRepository(
            $c[DoctrineConnectionFactory::class],
            $c[LoggerInterface::class],
        );
        $pimple[AccountRepository::class] = fn($c) => new CachingAccountRepository(
            $c[DoctrineAccountRepository::class],
        );
        $pimple[AccountService::class] = fn($c) => new AccountService($c[AccountRepository::class]);

        // Transactions
        $pimple[DoctrineTransactionRepository::class] = fn($c) => new DoctrineTransactionRepository(
            $c[DoctrineConnectionFactory::class],
            $c[AccountRepository::class],
            $c[LoggerInterface::class],
        );
        $pimple[TransactionRepository::class] = fn($c) => $c[DoctrineTransactionRepository::class];
        $pimple[TransactionService::class] = fn($c) => new TransactionService(
            $c[AccountService::class],
            $c[TransactionRepository::class],
            $c[LoggerInterface::class],
        );

        // Controllers
        $pimple[IndexController::class] = fn($c) => new IndexController(
            $c[ResponseGenerator::class],
            $c[UserRepository::class],
        );
        $pimple[ResponseGenerator::class] = fn($c) => new ResponseGenerator(
            $c[ResponseFactoryInterface::class],
            $c[TemplateEngine::class],
            $c[RouteConfiguration::class],
            $c[JwtAdapter::class],
            $c[JwtResponseTransformer::class],
        );
        $pimple[LoginController::class] = fn($c) => new LoginController(
            $c[ResponseGenerator::class],
            $c[UserService::class],
        );
        $pimple[PasswordController::class] = fn($c) => new PasswordController(
            $c[ResponseGenerator::class],
            $c[UserService::class],
        );
        $pimple[RegisterController::class] = fn($c) => new RegisterController(
            $c[ResponseGenerator::class],
            $c[UserRepository::class],
            $c[UserService::class],
        );
        $pimple[ResetController::class] = fn($c) => new ResetController(
            $c[ResponseGenerator::class],
            $c[UserService::class],
        );
        $pimple[AccountsController::class] = fn($c) => new AccountsController(
            $c[ResponseGenerator::class],
        );
        $pimple[ActivityController::class] = fn($c) => new ActivityController(
            $c[ResponseGenerator::class],
        );
        $pimple[UsersController::class] = fn($c) => new UsersController(
            $c[ResponseGenerator::class],
            $c[UserRepository::class],
        );
        $pimple[HelpController::class] = fn($c) => new HelpController(
            $c[ResponseGenerator::class],
        );
        $pimple[TransactionsController::class] = fn($c) => new TransactionsController(
            $c[TransactionRepository::class],
            $c[ResponseGenerator::class],
        );
        $pimple[TemplatesController::class] = fn($c) => new TemplatesController(
            $c[ResponseGenerator::class],
        );
        $pimple[TransactionController::class] = fn($c) => new TransactionController(
            $c[ResponseGenerator::class],
            $c[AccountRepository::class],
            $c[TransactionService::class],
            $c[TransactionRepository::class],
        );
        $pimple[MenuController::class] = fn($c) => new MenuController(
            $c[ResponseGenerator::class],
        );
        $pimple[UserController::class] = fn($c) => new UserController(
            $c[ResponseGenerator::class],
            $c[UserRepository::class],
            $c[UserService::class],
            $c[PasswordGenerator::class],
        );
    }
}
