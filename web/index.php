<?php

use JBZoo\PimpleDumper\PimpleDumper;
use Slim\App;
use Slim\Container;
use Taisiya\CoreBundle\Provider\CoreServiceProvider;

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR.'/vendor/autoload.php';

$app = new App(require ROOT_DIR.'/app/config/settings.php');

/** @var Container $container */
$container = $app->getContainer();
$container->register(new PimpleDumper()); // todo: register by settings value
$container->register(new CoreServiceProvider($app));

$app->run();
