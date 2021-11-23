<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\{
    Jwt\JwtAdapter,
    Jwt\JwtResponseTransformer,
    RouteConfiguration,
    Template\TemplateEngine,
    User\User,
};

use Psr\Http\Message\{
    ResponseFactoryInterface,
    ResponseInterface,
    ServerRequestInterface,
};

class ResponseGenerator
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private TemplateEngine $templateEngine,
        private RouteConfiguration $routes,
        private JwtAdapter $jwtAdapter,
        private JwtResponseTransformer $jwtResponseTransformer,
    ) { }

    /**
     * @param array<string, mixed> $data
     */
    public function render(
        ServerRequestInterface $request,
        string $template,
        array $data = [],
        int $status = 200,
    ): ResponseInterface {
        $params = array_merge((array) $request->getParsedBody(), $data);
        $body = $this->templateEngine->render($template, $params);
        $response = $this->responseFactory
                         ->createResponse($status)
                         ->withHeader('Content-Type', 'text/html');
        $response->getBody()->write($body);
        return $response;
    }

    public function redirect(string $route): ResponseInterface
    {
        return $this->responseFactory
                    ->createResponse(302)
                    ->withHeader('Location', $this->routes->getPath($route));
    }

    public function logIn(User $user): ResponseInterface
    {
        $userId = $user->getId();
        if ($userId === null) {
            return $this->redirect('get_login'); // @codeCoverageIgnore
        }
        $jwt = $this->jwtAdapter->encode(['sub' => $userId]);
        $response = $this->redirect('get_dashboard');
        return $this->jwtResponseTransformer->transform($response, $jwt);
    }
}
