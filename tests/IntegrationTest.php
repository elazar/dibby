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

it('redirects from registration page to login page when user exists', function () {
    $this->addUser();
    $request = $this->request(target: '/register');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/login');
});

it('displays error when registering with invalid input', function (array $body, string $error) {
    $request = $this->request(
        target: '/register',
        method: 'POST',
        body: $body,
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(400)
        ->toHaveBodyContaining($error);
})->with([
    'invalid e-mail' => [
        [
            'email' => 'foo',
            'password' => 'bar',
        ],
        'E-mail is invalid',
    ],
    'missing password' => [
        [
            'email' => 'foo@example.com',
            'password' => '',
        ],
        'Password is required',
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
        ],
    );
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/dashboard')
        ->toHaveCookie($this->config()->getSessionCookie());
});

it('redirects from landing page to login page when user is unauthenticated', function () {
    // Create a user to avoid being redirected to the registration page because none exist
    $this->addUser();
    $request = $this->request(target: '/');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/login');
});

it('redirects from landing page to dashboard page when user is authenticated', function () {
    $this->logIn();
    $request = $this->request(target: '/');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/dashboard');
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
        ->toHaveBodyContaining('Unrecognized e-mail or password.');
});

it('authenticates recognized user', function () {
    $response = $this->logIn();
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/dashboard');
});

it('displays password reset page', function () {
    $request = $this->request(target: '/password');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(200)
        ->toHaveHeader('Content-Type', 'text/html');
});

it('redirects from dashboard page to login page when user is unauthenticated', function () {
    $request = $this->request(target: '/dashboard');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/login');
});

it('displays dashboard page when user is authenticated', function () {
    $this->logIn();
    $request = $this->request(target: '/dashboard');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(200)
        ->toHaveHeader('Content-Type', 'text/html');
});

it('redirects to login page when JWT token cannot be decoded', function () {
    $cookie = [$this->config()->getSessionCookie() => 'foo'];
    $request = $this->request(target: '/dashboard', cookie: $cookie);
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/login');
    expect($this)->toHaveLog('warning', 'JWT token decoding failed');
});

it('redirects to login page when JWT token is missing subject', function () {
    $this->jwt(['iss' => 'foo']);
    $request = $this->request(target: '/dashboard');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/login');
    expect($this)->toHaveLog('warning', 'JWT token missing user');
});

it('redirects to login page when JWT token subject is not recognized', function () {
    $this->jwt(['sub' => 'foo']);
    $request = $this->request(target: '/dashboard');
    $response = $this->handle($request);
    expect($response)
        ->toHaveStatusCode(302)
        ->toHaveHeader('Location', '/login');
    expect($this)->toHaveLog('warning', 'Error retrieving JWT user');
});

it('displays error for unrecognized user on password reset page', function () {
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
        ->toHaveBodyContaining('Error: Unable to reset password for specified e-mail.');
});

it('displays confirmation for recognized user on password reset page', function () {
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
        ->toHaveBodyContaining('Password reset e-mail sent.');
    expect($this)
        ->toSendEmail($user->getEmail(), 'Dibby Password Reset');
});
