<?php

namespace Elazar\Dibby\Configuration;

use Elazar\Dibby\Database\DatabaseConfiguration;

class PhpArrayConfigurationFactory implements ConfigurationFactory
{
    public function __construct(
        private array $settings,
    ) { }

    public function getConfiguration(): Configuration
    {
        $db = $this->settings['db'];
        $read = $db['read'];
        $write = $db['write'];
        $baseUrl = $this->settings['base_url'];
        $fromEmail = $this->settings['from_email'];
        $resetTokenTtl = $this->settings['reset_token_ttl'];
        $session = $this->settings['session'];
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
