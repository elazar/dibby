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
        /** @var array<string, array<string, string>> $db */
        $db = $this->settings['db'];
        $databaseReadConfiguration = $this->getDatabaseConfiguration($db['read']);
        $databaseWriteConfiguration = $this->getDatabaseConfiguration($db['write']);

        return new Configuration(
            $databaseReadConfiguration,
            $databaseWriteConfiguration,
            $this->settings['base_url'],
            $this->settings['from_email'],
            $this->settings['session']['key'],
            $this->settings['session']['cookie'],
            $this->settings['session']['ttl'],
            $this->settings['session']['secure'],
            $this->settings['reset_token_ttl'],
            $this->settings['smtp']['host'],
            $this->settings['smtp']['port'],
            $this->settings['smtp']['username'] ?? null,
            $this->settings['smtp']['password'] ?? null,
            $this->settings['smtp']['tls'] ?? false,
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
