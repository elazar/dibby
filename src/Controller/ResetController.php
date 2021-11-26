<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\User\UserService;

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

use Throwable;

class ResetController
{
    public function __construct(
        private ResponseGenerator $responseGenerator,
        private UserService $userService,
    ) { }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];
        $status = 200;

        if (strcasecmp($request->getMethod(), 'post') === 0) {
            $body = (array) $request->getParsedBody();
            $userId = $body['user'] ?? '';
            $resetToken = $body['token'] ?? '';
            $password = $body['password'] ?? '';
            try {
                $user = $this->userService->completePasswordReset($userId, $resetToken, $password);
                if ($user === null) {
                    $data['error'] = 'Error: Password reset link expired or invalid.';
                    $status = 400;
                } else {
                    $data['success'] = true;
                }
            } catch (Throwable $error) {
                $data['error'] = 'Error: ' . $error->getMessage();
                $status = 400;
            }
        } else {
            $query = $request->getQueryParams();
            $userId = $query['user'] ?? '';
            $resetToken = $query['token'] ?? '';
            $user = $this->userService->verifyPasswordReset($userId, $resetToken);
            if ($user === null) {
                $data['error'] = 'Error: Password reset link expired or invalid.';
                $status = 400;
            } else {
                $data += [
                    'user' => $userId,
                    'token' => $resetToken,
                ];
            }
        }

        return $this->responseGenerator->render(
            request: $request,
            template: 'reset',
            data: $data,
            status: $status,
        );
    }
}
