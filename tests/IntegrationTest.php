<?php

it('returns a correct response for a nonexistent route', function () {
    $request = $this->request(target: '/foo');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(404);
});

it('redirects from landing page to registration page when no users exist', function () {
    $request = $this->request(target: '/');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/register');
});

it('redirects unauthenticated user to login page when user exists', function (string $target) {
    $this->addUser();
    $request = $this->request(target: $target);
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/login');
})->with([
    '/',
    '/menu',
    '/register',
    '/transactions',
    '/users',
    '/users/add',
]);

it('displays error when registering with invalid input', function (array $body, string $error) {
    $request = $this->request(
        target: '/register',
        method: 'POST',
        body: $body,
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(400)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining($error);
})->with([
    'invalid e-mail' => [
        [
            'email' => 'foo',
            'password' => 'bar',
            'name' => 'baz',
        ],
        'E-mail is invalid',
    ],
    'missing password' => [
        [
            'email' => 'foo@example.com',
            'password' => '',
            'name' => 'baz',
        ],
        'Password is required',
    ],
    'missing name' => [
        [
            'email' => 'foo@example.com',
            'password' => 'bar',
            'name' => '',
        ],
        'Name is required',
    ],
]);

it('registers valid user when no users exist', function () {
    $user = $this->newUser();
    $request = $this->request(
        target: '/register',
        method: 'POST',
        body: [
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'name' => $user->getName(),
        ],
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/transactions')
        ->toHaveCookie($this->config()->getSessionCookie());
});

it('redirects from landing page to transactions page when user is authenticated', function () {
    $this->logIn();
    $request = $this->request(target: '/');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/transactions');
});

it('displays login page', function () {
    $request = $this->request(target: '/login');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(200)
        ->toHaveHeader('Content-Type', 'text/html');
});

it('displays error for unrecognized user on login page', function () {
    $user = $this->newUser();
    $response = $this->logIn($user);
    expect($response)
        ->toHaveStatusCode(403)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining('Unrecognized e-mail or password');
});

it('authenticates recognized user', function () {
    $response = $this->logIn();
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/transactions');
});

it('displays password page', function () {
    $request = $this->request(target: '/password');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(200)
        ->toHaveHeader('Content-Type', 'text/html');
});

it('displays transactions page when user is authenticated', function () {
    $this->logIn();
    $request = $this->request(target: '/transactions');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(200)
        ->toHaveHeader('Content-Type', 'text/html');
});

it('redirects to login page when JWT token cannot be decoded', function () {
    $cookie = [$this->config()->getSessionCookie() => 'foo'];
    $request = $this->request(target: '/transactions', cookie: $cookie);
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/login');
    expect($this)->toHaveLog('warning', 'JWT token decoding failed');
});

it('redirects to login page when JWT token is missing subject', function () {
    $this->jwt(['iss' => 'foo']);
    $request = $this->request(target: '/transactions');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/login');
    expect($this)->toHaveLog('warning', 'JWT token missing user');
});

it('redirects to login page when JWT token subject is not recognized', function () {
    $this->jwt(['sub' => 'foo']);
    $request = $this->request(target: '/transactions');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/login');
    expect($this)->toHaveLog('warning', 'Error retrieving JWT user');
});

it('displays error for unrecognized user on password page', function () {
    $user = $this->newUser();
    $request = $this->request(
        target: '/password',
        method: 'POST',
        body: [
            'email' => $user->getEmail(),
        ],
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(400)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining('Error: Unable to reset password for specified e-mail');
});

it('displays confirmation for recognized user on password page', function () {
    $user = $this->addUser();
    $request = $this->request(
        target: '/password',
        method: 'POST',
        body: [
            'email' => $user->getEmail(),
        ],
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(200)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining('Password reset e-mail sent');
    expect($this)
        ->toSendEmail($user->getEmail(), 'Dibby Password Reset');
});

it('displays error when loading reset page with invalid parameters', function () {
    $request = $this->request(
        target: '/reset',
        query: [
            'user' => 'foo',
            'token' => 'bar',
        ],
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(400)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining('Error: Password reset link expired or invalid');
});

it('displays error when submitting reset form with invalid parameters', function () {
    $request = $this->request(
        target: '/reset',
        method: 'POST',
        body: [
            'user' => 'foo',
            'token' => 'bar',
            'password' => 'baz',
        ],
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(400)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining('Error: Password reset link expired or invalid');
});

it('displays error when loading reset page with expired token', function () {
    $token = 'foo';
    $user = $this->addUser(
        $this->newUser()
             ->withResetToken($token)
             ->withResetTokenExpiration(
                 new DateTimeImmutable('2021-11-23T19:00:00Z'),
             ),
    );
    $request = $this->request(
        target: '/reset',
        query: [
            'user' => $user->getId(),
            'token' => $token,
        ],
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(400)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining('Error: Password reset link expired or invalid');
});

it('displays error when submitting reset form with expired token', function () {
    $token = 'foo';
    $user = $this->addUser(
        $this->newUser()
             ->withResetToken($token)
             ->withResetTokenExpiration(
                 new DateTimeImmutable('2021-11-23T19:00:00Z'),
             ),
    );
    $request = $this->request(
        target: '/reset',
        method: 'POST',
        body: [
            'user' => $user->getId(),
            'token' => $token,
            'password' => 'baz',
        ],
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(400)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining('Error: Password reset link expired or invalid');
});

it('displays reset page with valid parameters', function () {
    $token = 'foo';
    $user = $this->addUser(
        $this->newUser()
             ->withResetToken($token)
             ->withResetTokenExpiration(
                 new DateTimeImmutable('+30 minutes'),
             ),
    );
    $request = $this->request(
        target: '/reset',
        query: [
            'user' => $user->getId(),
            'token' => $token,
        ],
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(200)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining('New Password');
});

it('displays error when submitting reset form with empty password', function () {
    $token = 'foo';
    $user = $this->addUser(
        $this->newUser()
             ->withResetToken($token)
             ->withResetTokenExpiration(
                 new DateTimeImmutable('+30 minutes'),
             ),
    );
    $request = $this->request(
        target: '/reset',
        method: 'POST',
        body: [
            'user' => $user->getId(),
            'token' => $token,
            'password' => '',
        ],
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(400)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining('Error: Password is required');
});

it('resets password successfully', function () {
    $token = 'foo';
    $password = 'foobar';
    $user = $this->addUser(
        $this->newUser()
             ->withResetToken($token)
             ->withResetTokenExpiration(
                 new DateTimeImmutable('+30 minutes'),
             ),
    );
    $request = $this->request(
        target: '/reset',
        method: 'POST',
        body: [
            'user' => $user->getId(),
            'token' => $token,
            'password' => $password,
        ],
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(200)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining('Password reset successfully');
    $response = $this->logIn($user);
    expect($response)
        ->toHaveStatusCode(403)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining('Unrecognized e-mail or password');
    $response = $this->logIn($user->withPassword($password));
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/transactions');
});

it('lists the registered user on the users page', function () {
    $user = $this->addUser();
    $this->logIn($user);
    $request = $this->request(target: '/users');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(200)
        ->toHaveBodyMatching('td:contains(' . $user->getName() . ')');
});

it('prevents a user from being added with invalid data', function (array $body, string $error) {
    $this->logIn();
    $request = $this->request(
        target: '/users',
        method: 'POST',
        body: $body,
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(400)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyContaining($error);
})->with([
    'missing name' => [
        [
            'name' => '',
            'email' => 'foo@bar.com',
        ],
        'Name is required',
    ],
    'invalid e-mail' => [
        [
            'name' => 'baz',
            'email' => 'foo',
        ],
        'E-mail is invalid',
    ],
]);

it('allows the registered user to add another user', function () {
    $this->logIn();
    $request = $this->request(
        target: '/users',
        method: 'POST',
        body: [
            'name' => 'Jane',
            'email' => 'jane@example.com',
        ],
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/users');
});

it('displays a form to edit a user', function () {
    $user = $this->addUser();
    $this->login($user);
    $request = $this->request(target: '/users/' . $user->getId());
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(200)
        ->toHaveHeader('Content-Type', 'text/html')
        ->toHaveBodyMatching('input[name=id][value="' . $user->getId() . '"]')
        ->toHaveBodyMatching('input[name=name][value="' . $user->getName() . '"]')
        ->toHaveBodyMatching('input[name=email][value="' . $user->getEmail() . '"]');
});

it('displays menu when user is authenticated', function () {
    $this->logIn();
    $request = $this->request(target: '/menu');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(200)
        ->toHaveHeader('Content-Type', 'text/html');
});
