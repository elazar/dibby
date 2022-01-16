<?php

use Elazar\Dibby\{
    Configuration\Configuration,
    Configuration\PhpArrayConfigurationFactory,
    Database\DatabaseConfiguration,
};

function php_array_configuration(): array
{
    return [
        'db' => [
            'read' => [
                'driver' => 'read_driver',
                'host' => 'read_host',
                'port' => '1234',
                'user' => 'read_user',
                'password' => 'read_password',
                'name' => 'read_name',
            ],
            'write' => [
                'driver' => 'write_driver',
                'host' => 'write_host',
                'port' => '5678',
                'user' => 'write_user',
                'password' => 'write_password',
                'name' => 'write_name',
            ],
        ],
        'base_url' => 'base_url',
        'from_email' => 'from_email',
        'session' => [
            'key' => 'key',
            'cookie' => 'cookie',
            'ttl' => 'PT30M',
            'secure' => true,
        ],
        'reset_token_ttl' => 'PT30M',
        'smtp' => [
            'host' => 'host',
            'port' => '1234',
        ],
    ];
}

it('fails to load with missing required setting', function (array $settings, string $message) {
    try {
        (new PhpArrayConfigurationFactory($settings))->getConfiguration();
    } catch (Throwable $error) {
        expect($error->getMessage())->toBe($message);
    }
})->with(function () {
    /**
     * @param string[] $keys
     * @return array<string, array|string>
     */
    $without = function (array $keys): array {
        $configuration = php_array_configuration();
        $last = array_pop($keys);
        $reference = &$configuration;
        foreach ($keys as $key) {
            $reference = &$reference[$key];
        }
        unset($reference[$last]);
        return $configuration;
    };

    /**
     * @param array<string, array|string> $subset
     * @param string[] $keys
     * @return array<string, array|string>
     */
    $generate = function (array $subset, array $keys = []) use ($without, &$generate): array {
        $generated = [];
        foreach ($subset as $key => $value) {
            $path = [...$keys, $key];
            if (is_array($value)) {
                $generated = array_merge($generated, $generate($value, $path));
            } else {
                $generated[implode('.', $path)] = [ $without($path), "Undefined array key \"$key\"" ];
            }
        }
        return $generated;
    };

    yield from $generate(php_array_configuration());
});

it('loads with all required settings', function () {
    $array = php_array_configuration();
    $factory = new PhpArrayConfigurationFactory($array);
    $configuration = $factory->getConfiguration();
    expect($configuration)->toBeInstanceOf(Configuration::class);

    $read = $configuration->getDatabaseReadConfiguration();
    expect($read)->toBeInstanceOf(DatabaseConfiguration::class);
    expect($read->getDriver())->toBe($array['db']['read']['driver']);
    expect($read->getHost())->toBe($array['db']['read']['host']);
    expect($read->getPort())->toEqual($array['db']['read']['port']);
    expect($read->getUser())->toEqual($array['db']['read']['user']);
    expect($read->getPassword())->toEqual($array['db']['read']['password']);
    expect($read->getName())->toEqual($array['db']['read']['name']);

    $write = $configuration->getDatabaseWriteConfiguration();
    expect($write)->toBeInstanceOf(DatabaseConfiguration::class);
    expect($write->getDriver())->toBe($array['db']['write']['driver']);
    expect($write->getHost())->toBe($array['db']['write']['host']);
    expect($write->getPort())->toEqual($array['db']['write']['port']);
    expect($write->getUser())->toEqual($array['db']['write']['user']);
    expect($write->getPassword())->toEqual($array['db']['write']['password']);
    expect($write->getName())->toEqual($array['db']['write']['name']);

    expect($configuration->getBaseUrl())->toBe($array['base_url']);
    expect($configuration->getFromEmail())->toBe($array['from_email']);
    expect($configuration->getResetTokenTimeToLive())->toBe($array['reset_token_ttl']);

    expect($configuration->getSessionKey())->toBe($array['session']['key']);
    expect($configuration->getSessionCookie())->toBe($array['session']['cookie']);
    expect($configuration->getSessionTimeToLive())->toBe($array['session']['ttl']);
    expect($configuration->getSessionSecure())->toBe($array['session']['secure']);

    expect($configuration->getSmtpHost())->toBe($array['smtp']['host']);
    expect($configuration->getSmtpPort())->toEqual($array['smtp']['port']);
});

it('loads optional settings', function () {
    $array = php_array_configuration();
    $array['smtp'] += [
        'username' => 'username',
        'password' => 'password',
        'tls' => true,
    ];

    $factory = new PhpArrayConfigurationFactory($array);
    $configuration = $factory->getConfiguration();
    expect($configuration)->toBeInstanceOf(Configuration::class);

    expect($configuration->getSmtpUsername())->toBe($array['smtp']['username']);
    expect($configuration->getSmtpPassword())->toBe($array['smtp']['password']);
    expect($configuration->getSmtpTls())->toBe($array['smtp']['tls']);
});
