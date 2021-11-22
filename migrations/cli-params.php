<?php
require __DIR__ . '/../vendor/autoload.php';
$container = new \Pimple\Container;
$container->register(new \Elazar\Dibby\PimpleServiceProvider);
return $container[\Elazar\Dibby\Database\Migrations\CliConfig::class]->getParams();
