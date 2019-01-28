<?php

use DI\ContainerBuilder;
require __DIR__ . '/vendor/autoload.php';
$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/vendor/php-di/slim-bridge/src/config.php');
$builder->addDefinitions(__DIR__ . '/di.php');
return $builder->build();
