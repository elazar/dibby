<?php

namespace Elazar\Dibby\Email;

use Elazar\Dibby\Configuration\Configuration;
use Laminas\Mail\Protocol\Smtp\Auth\Plain;
use Laminas\Mail\Transport\SmtpOptions;

class LaminasSmtpOptionsFactory
{
    public function __construct(
        private Configuration $configuration,
    ) { }

    public function getSmtpOptions(): SmtpOptions
    {
        $options = [
            'host' => $this->configuration->getSmtpHost(),
            'port' => (int) $this->configuration->getSmtpPort(),
        ];

        if ($username = $this->configuration->getSmtpUsername()) {
            $options['connectionClass'] = Plain::class;
            $config = [
                'username' => $username,
            ];
            if ($password = $this->configuration->getSmtpPassword()) {
                $config['password'] = $password;
            }
            if ($this->configuration->getSmtpTls()) {
                $config['ssl'] = 'tls';
            }
            $options['connectionConfig'] = $config;
        }

        return new SmtpOptions($options);
    }
}
