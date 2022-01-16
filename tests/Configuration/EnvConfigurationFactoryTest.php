<?php

use Elazar\Dibby\{
    Configuration\Configuration,
    Configuration\EnvConfigurationFactory,
    Database\DatabaseConfiguration,
};

function env_configuration(array $without = []): array
{
    $env = [
        'DB_READ_DRIVER' => 'read_driver',
        'DB_READ_HOST' => 'read_host',
        'DB_READ_PORT' => 1234,
        'DB_READ_USER' => 'read_user',
        'DB_READ_PASSWORD' => 'read_password',
        'DB_READ_NAME' => 'read_name',
        'DB_WRITE_DRIVER' => 'write_driver',
        'DB_WRITE_HOST' => 'write_host',
        'DB_WRITE_PORT' => 5678,
        'DB_WRITE_USER' => 'write_user',
        'DB_WRITE_PASSWORD' => 'write_password',
        'DB_WRITE_NAME' => 'write_name',
        'BASE_URL' => 'base_url',
        'FROM_EMAIL' => 'from@email.com',
        'SESSION_KEY' => 'session_key',
        'SESSION_COOKIE' => 'session_cookie',
        'SESSION_TTL' => 'PT30M',
        'SESSION_SECURE' => true,
        'RESET_TOKEN_TTL' => 'PT1H',
        'SMTP_HOST' => 'smtp_host',
        'SMTP_PORT' => 9012,
        'SMTP_USERNAME' => 'smtp_username',
        'SMTP_PASSWORD' => 'smtp_password',
        'SMTP_TLS' => 'smtp_tls',
    ];

    if (!empty($without)) {
        foreach ($without as $key) {
            unset($env[$key]);
        }
    }

    return array_combine(
        array_map(
            fn (string $key): string => "DIBBY_$key",
            array_keys($env),
        ),
        array_values($env)
    );
}

it('fails to load with missing required setting', function (array $settings, string $message) {
    foreach (env_configuration() as $key => $value) {
        $cmd = $key;
        if (in_array($key, $settings)) {
            $cmd .= "=$value";
        }
        putenv($cmd);
    }
    try {
        (new EnvConfigurationFactory())->getConfiguration();
    } catch (Throwable $error) {
        expect($error->getMessage())->toBe($message);
    }
})->with(function () {
    $configuration = env_configuration(without: [
        'SMTP_USERNAME',
        'SMTP_PASSWORD',
        'SMTP_TLS',
    ]);
    foreach (array_keys($configuration) as $key) {
        $copy = $configuration;
        unset($copy[$key]);
        yield $key => [ array_keys($copy), "Environmental variable '$key' is undefined" ];
    }
});
