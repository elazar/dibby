<?php

namespace Elazar\Dibby\Controller;

use Elazar\Dibby\User\{
    PasswordGenerator,
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
        private PasswordGenerator $passwordGenerator,
    ) { }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
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
                $user = User::fromArray($body);
                if (empty($user->getPassword())) {
                    $user = $user->withPassword(
                        $this->passwordGenerator->getPassword($user),
                    );
                }
                $user = $this->userService->persistUser($user);
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
