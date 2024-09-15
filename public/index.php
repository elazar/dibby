<?php

require __DIR__ . '/../vendor/autoload.php';

$container = new \Pimple\Container;
$container->register(new \Elazar\Dibby\PimpleServiceProvider);
$container[\Elazar\Dibby\Application::class]->run();
