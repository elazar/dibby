<?php

namespace Elazar\Dibby\Configuration;

interface ConfigurationFactory
{
    public function getConfiguration(): Configuration;
}
