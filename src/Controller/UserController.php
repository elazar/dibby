<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\User\{
    User,
    UserRepository,
    UserService,
};

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};

use Throwable;

class UserController
{
    public function __construct(
        private ResponseGenerator $responseGenerator,
        private UserRepository $userRepository,
        private UserService $userService,
    ) { }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var ?\Elazar\Dibby\User\User $user */
        $user = $request->getAttribute('user');
        if ($user === null) {
            return $this->responseGenerator->redirect('get_login');
        }

        $data = [
            'user' => $user,
        ];
        $status = 200;

        if (strcasecmp($request->getMethod(), 'post') === 0) {
            $body = (array) $request->getParsedBody();
            try {
                $user = $this->userService->persistUser(
                    User::fromArray($body),
                );
                return $this->responseGenerator->redirect('get_users');
            } catch (Throwable $error) {
                $data += $body;
                $data['error'] = $error->getMessage();
                $status = 400;
            }
        } elseif (isset($args['userId'])) {
            $user = $this->userRepository->getUserById($args['userId']);
            $data += [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ];
        }

        return $this->responseGenerator->render(
            request: $request,
            template: 'user',
            data: $data,
            status: $status,
        );
    }
}
