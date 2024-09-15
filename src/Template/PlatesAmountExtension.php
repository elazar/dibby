<?php

namespace Elazar\Dibby\Template;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PlatesAmountExtension implements ExtensionInterface
{
    public function register(Engine $engine)
    {
        $engine->registerFunction(
            'formatAmount',
            fn(float $amount): string => number_format($amount, 2),
        );
    }
}
