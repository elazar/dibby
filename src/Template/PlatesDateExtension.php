<?php

namespace Elazar\Dibby\Template;

use DateTimeInterface;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PlatesDateExtension implements ExtensionInterface
{
    public function register(Engine $engine)
    {
        $engine->registerFunction(
            'formatDate',
            fn(?DateTimeInterface $date): string => $date ? $date->format('D, M j, Y') : 'Pending',
        );
    }
}
