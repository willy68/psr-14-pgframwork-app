<?php

declare(strict_types=1);

use PgFramework\Environnement\Environnement;
use PgFramework\File\FileUtils;
use Symfony\Component\Dotenv\Dotenv;

if (!class_exists(Dotenv::class)) {
    throw new Exception("le library symfony/dotenv est pas installée, lancez composer symfony/dotenv!");
}

if (!isset($basePath)) {
    $basePath = dirname(__DIR__, 2);
}

$dotenv = new Dotenv();
$dotenv->bootEnv($basePath . '/.env');

$bootstrap = require 'App.php';

$config = FileUtils::getFiles($basePath . '/config', 'php', '.dist.');

$app = (new PgFramework\App(array_keys($config)))
    ->addModules($bootstrap['modules'])
    //->addMiddlewares($bootstrap['middlewares']);
    ->addListeners($bootstrap['listeners']);

if (Environnement::getEnv('APP_ENV', 'prod') === 'dev') {
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->register();
}

return $app;
