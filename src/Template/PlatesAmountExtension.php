<?php

namespace Elazar\Dibby\Template;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PlatesAmountExtension implements ExtensionInterface
{
    /**
     * @return void
     */
    public function register(Engine $engine)
    {
        /**
         * PHPStan flags the error below. It's fixed in the v3 branch of Plates
         * but not in a tagged release. Ignoring it until there's a new release
         * that includes it.
         *
         * Parameter #2 $callback of method
         * League\Plates\Engine::registerFunction() expects
         * League\Plates\callback, Closure given.
         *
         * @see https://github.com/thephpleague/plates/commit/11a58264fde1c1c8a2e6da7595f0a4614d472302
         */
        $engine->registerFunction(
            'formatAmount',
            /** @phpstan-ignore-next-line */
            fn(float $amount): string => number_format($amount, 2),
        );
    }
}