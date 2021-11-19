<?php

namespace Elazar\Dibby\Configuration;

use Elazar\Dibby\Database\DatabaseConfiguration;

class PhpConfigurationFactory implements ConfigurationFactory
{
    public function __construct(
        private ?string $path = null,
    ) {
        if ($this->path === null) {
            $this->path = __DIR__ . '/../../config.php';
        }
    }

    public function getConfiguration(): Configuration
    {
        $settings = require $this->path;

        $databaseReadConfiguration = $this->getDatabaseConfiguration($settings['db']['read']);

        $databaseWriteConfiguration = $this->getDatabaseConfiguration($settings['db']['write']);

        return new Configuration(
            $databaseReadConfiguration,
            $databaseWriteConfiguration,
            $settings['session']['key'],
            (int) $settings['session']['ttl'],
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
