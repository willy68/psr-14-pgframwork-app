<?php

use PgFramework\Environnement\Environnement;
use Middlewares\Whoops;
use Symfony\Component\Dotenv\Dotenv;

if (!class_exists(Dotenv::class)) {
    throw new Exception("le library symfony/dotenv est pas installée, lancez composer symfony/dotenv!");
}

if (!isset($basePath)) {
    $basePath = dirname(dirname(__DIR__));
}

$dotenv = new Dotenv();
$dotenv->bootEnv($basePath . '/.env');

$bootstrap = require 'App.php';

$app = (new PgFramework\App($bootstrap['config']))
    ->addModules($bootstrap['modules'])
    ->addMiddlewares($bootstrap['middlewares']);
    //->addListeners($bootstrap['listeners']);

if (Environnement::getEnv('APP_ENV', 'production') === 'dev') {
    //$app->pipe(Whoops::class);
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->register();
}

return $app;
