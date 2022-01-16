<?php

namespace Elazar\Dibby\Configuration;

use Elazar\Dibby\Database\DatabaseConfiguration;

class PhpArrayConfigurationFactory implements ConfigurationFactory
{
    /**
     * @param array<string, string|array<string, string>> $settings
     */
    public function __construct(
        private array $settings,
    ) { }

    public function getConfiguration(): Configuration
    {
        /** @var array<string, array<string, string>|string> $db */
        $db = $this->settings['db'];
        /** @var array<string, string> $read */
        $read = $db['read'];
        /** @var array<string, string> $write */
        $write = $db['write'];
        /** @var string */
        $baseUrl = $this->settings['base_url'];
        /** @var string */
        $fromEmail = $this->settings['from_email'];
        /** @var string */
        $resetTokenTtl = $this->settings['reset_token_ttl'];
        /** @var array<string, string> */
        $session = $this->settings['session'];
        /** @var array<string, string> */
        $smtp = $this->settings['smtp'];

        $databaseReadConfiguration = $this->getDatabaseConfiguration($read);
        $databaseWriteConfiguration = $this->getDatabaseConfiguration($write);

        return new Configuration(
            $databaseReadConfiguration,
            $databaseWriteConfiguration,
            $baseUrl,
            $fromEmail,
            $session['key'],
            $session['cookie'],
            $session['ttl'],
            (bool) $session['secure'],
            $resetTokenTtl,
            $smtp['host'],
            (int) $smtp['port'],
            $smtp['username'] ?? null,
            $smtp['password'] ?? null,
            isset($smtp['tls']) ? (bool) $smtp['tls'] : false,
        );
    }

    /**
     * @param array<string, string> $settings
     */
    private function getDatabaseConfiguration(array $settings): DatabaseConfiguration
    {
        return new DatabaseConfiguration(
            $settings['driver'],
            $settings['host'],
            (int) $settings['port'],
            $settings['user'],
            $settings['password'],
            $settings['name'],
        );
    }
}
