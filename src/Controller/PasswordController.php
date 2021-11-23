<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\User\UserService;

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

class PasswordController
{
    public function __construct(
        private ResponseGenerator $responseGenerator,
        private UserService $userService,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $data = ['title' => 'Forgot Password'];
        $status = 200;

        if (strcasecmp($request->getMethod(), 'post') === 0) {
            $body = (array) $request->getParsedBody();
            $user = $this->userService->startPasswordReset($body['email'] ?? '');
            if ($user === null) {
                $data['message'] = 'Error: Unable to reset password for specified e-mail.';
                $status = 400;
            } else {
                $data['message'] = 'Password reset e-mail sent.';
            }
        }

        return $this->responseGenerator->render(
            request: $request,
            template: 'password',
            data: $data,
            status: $status,
        );
    }
}
