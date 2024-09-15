<?php

namespace Elazar\Dibby\Configuration;

class PhpFileConfigurationFactory implements ConfigurationFactory
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
        return (new PhpArrayConfigurationFactory($settings))->getConfiguration();
    }
}
