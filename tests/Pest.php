<?php

namespace Elazar\Dibby;

use Elazar\Dibby\{
    Application,
    Configuration\Configuration,
    Database\DoctrineConnectionFactory,
    Jwt\JwtAdapter,
    LoggerMiddleware,
    PimpleServiceProvider,
    User\DefaultPasswordHasher,
    User\User,
    User\UserService,
};

use Laminas\Mail\{
    Message,
    Transport\InMemory,
    Transport\TransportInterface,
};

use League\Route\Router;

use Monolog\{
    Handler\TestHandler,
    Logger,
};

use Nyholm\Psr7Server\ServerRequestCreatorInterface;

use Pimple\{
    Container,
    Psr11\Container as PsrContainer,
};

use Psr\Log\LoggerInterface;

use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface,
};

use Symfony\Component\DomCrawler\Crawler;

/**
 * @return array<string, string>
 */
function getResponseCookies(ResponseInterface $response): array
{
    return array_reduce(
        $response->getHeader('Set-Cookie'),
        /**
         * @param array<string, string> $cookies
         * @return array<string, string>
         */
        function (array $cookies, string $header): array {
            if (preg_match('/([^=]+)=([^\s;]+)/', $header, $match)) {
                [, $name, $value] = $match;
                $cookies[$name] = $value;
            }
            return $cookies;
        },
        [],
    );
}

expect()->extend('toHaveStatusCode', function (int $statusCode) {
    expect($this->value->getStatusCode())->toBe($statusCode);
    return $this;
});

expect()->extend('toHaveHeader', function (string $header, string $value) {
    expect($this->value->getHeaderLine($header))->toBe($value);
    return $this;
});

expect()->extend('toHaveBodyContaining', function (string $needle) {
    $body = $this->value->getBody();
    $body->rewind();
    expect($body->getContents())->toContain($needle);
    return $this;
});

expect()->extend('toHaveBodyMatching', function (string $pattern) {
    $body = $this->value->getBody();
    $body->rewind();
    $crawler = new Crawler($body->getContents());
    $count = $crawler->filter($pattern)->count();
    expect($count)->toBeGreaterThan(0);
    return $this;
});

expect()->extend('toHaveCookie', function (string $name) {
    $cookies = getResponseCookies($this->value);
    expect($cookies)->toHaveKey($name);
    return $this;
});

expect()->extend('toHaveLog', function (string $level, string $message) {
    expect($this->value->logHandler())->hasRecord($message, $level);
    return $this;
});

expect()->extend('toSendEmail', function (string $toEmail, string $subject) {
    $message = $this->value->lastEmail();
    expect($message->getTo()->has($toEmail))->toBeTrue();
    expect($message->getSubject())->toBe($subject);
    return $this;
});

trait TestHelpers
{
    private ?PsrContainer $container = null;

    private ?ServerRequestInterface $emptyRequest = null;

    private ?ServerRequestInterface $lastRequest = null;

    private ?InMemory $emailTransport = null;

    private ?array $cookie = null;

    public function newUser(
        string $email = 'foo@example.com',
        string $password = 'bar',
        string $name = 'John',
    ): User {
        return (new User($email))
            ->withPassword($password)
            ->withName($name);
    }

    public function addUser(?User $user = null): User
    {
        if ($user === null) {
            $user = $this->newUser();
        }
        return $this->get(UserService::class)->persistUser($user);
    }

    public function get(string $key): mixed
    {
        if ($this->container === null) {
            $container = new Container;
            $container->register(new PimpleServiceProvider);

            // Use in-memory transport to allow e-mails to be inspected
            $this->emailTransport = new InMemory;
            $container[TransportInterface::class] = fn() => $this->emailTransport;

            // Add test handler to logger to allow logs to be inspected
            $container[TestHandler::class] = fn() => new TestHandler;
            $container[Logger::class] = function ($c) {
                $logger = new Logger('dibby-tests');
                /** @var TestHandler */
                $handler = $c[TestHandler::class];
                $logger->pushHandler($handler);
                return $logger;
            };

            // Use a middleware to log requests, responses, and exceptions for inspection
            $container[LoggerMiddleware::class] = fn($c) => new LoggerMiddleware(
                $c[LoggerInterface::class],
            );
            $container->extend(Router::class, function (Router $router, $c) {
                /** @var LoggerMiddleware */
                $middleware = $c[LoggerMiddleware::class];
                $router->middleware($middleware);
                return $router;
            });

            /**
             * Minimize password hashing cost to reduce impact on test runtime.
             *
             * "The... cost parameter... must be in range 04-31..."
             *
             * @see https://www.php.net/crypt
             */
            $container[DefaultPasswordHasher::class] = new DefaultPasswordHasher(cost: 4);

            // Make the "current" request configurable
            $this->emptyRequest = $container[ServerRequestCreatorInterface::class]->fromArrays([
                'REQUEST_METHOD' => 'GET',
            ]);
            $this->lastRequest = clone $this->emptyRequest;
            $container[ServerRequestInterface::class] = fn() => $this->lastRequest;

            $this->container = new PsrContainer($container);
        }

        return $this->container->get($key);
    }

    /**
     * @param ?array<string, string> $body
     * @param array<string, string> $headers
     * @param ?array<string, string> $query
     * @param ?array<string, string> $cookie
     */
    public function request(
        string $target,
        string $method = 'GET',
        ?array $body = null,
        array $headers = [],
        ?array $query = null,
        ?array $cookie = null,
        ?User $user = null,
    ): ServerRequestInterface {
        $request = (clone $this->emptyRequest)
            ->withRequestTarget($target)
            ->withUri($this->emptyRequest->getUri()->withPath($target))
            ->withMethod($method);

        if ($body !== null) {
            $request = $request->withParsedBody($body);
        }

        foreach ($headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        if ($query !== null) {
            $request = $request->withQueryParams($query);
        }

        if ($cookie !== null) {
            $request = $request->withCookieParams($cookie);
        } elseif ($this->cookie !== null) {
            $request = $request->withCookieParams($this->cookie);
        }

        if ($user !== null) {
            $request = $request->withAttribute('user', $user);
        }

        $this->lastRequest = $request;

        return $request;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Appliation */
        $app = $this->get(Application::class);
        $response = $app->handle($request);
        $this->cookie = getResponseCookies($response);
        return $response;
    }

    public function reset(): void
    {
        /** @var DoctrineConnectionFactory */
        $connectionFactory = $this->get(DoctrineConnectionFactory::class);
        $connection = $connectionFactory->getWriteConnection();

        $tables = [
            'user',
        ];
        foreach ($tables as $table) {
            $connection
                ->createQueryBuilder()
                ->delete($connection->quoteIdentifier($table))
                ->executeStatement();
        }

        $this->container = null;
        $this->cookie = null;
    }

    public function logIn(?User $user = null): ResponseInterface
    {
        if ($user === null) {
            $user = $this->addUser();
        }

        $request = $this->request(
            target: '/login',
            method: 'POST',
            body: [
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
            ],
        );

        return $this->handle($request);
    }

    public function config(): Configuration
    {
        return $this->get(Configuration::class);
    }

    public function jwt(array $payload): void
    {
        $token = $this->get(JwtAdapter::class)->encode($payload);
        $this->cookie[$this->config()->getSessionCookie()] = $token;
    }

    public function logHandler(): TestHandler
    {
        /** @var TestHandler */
        return $this->get(TestHandler::class);
    }

    public function lastEmail(): ?Message
    {
        return $this->emailTransport?->getLastMessage();
    }

    public function logs(): void
    {
        expect($this->logHandler()->getRecords())->dd();
    }
}

uses(TestHelpers::class)->in(__DIR__);

uses()
    ->beforeEach(function () {
        $this->reset();
    })
    ->in(__DIR__);
