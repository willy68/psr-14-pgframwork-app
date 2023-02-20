<?php

declare(strict_types=1);

use PgFramework\App;
use PgFramework\Environnement\Environnement;
use Symfony\Component\Dotenv\Dotenv;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

return (static function (): App {
    if (!class_exists(Dotenv::class)) {
        throw new Exception("le library symfony/dotenv est pas installée, lancez composer symfony/dotenv!");
    }

    $app = new App();

    if (!isset($basePath)) {
        $basePath = $app->getProjectDir();
    }

    $dotenv = new Dotenv();
    $dotenv->bootEnv($basePath . '/.env');

    $bootstrap = require 'App.php';

    $app
        ->addModules($bootstrap['modules'])
        //->addMiddlewares($bootstrap['middlewares']);
        ->addListeners($bootstrap['listeners']);

    if (Environnement::getEnv('APP_ENV', 'prod') === 'dev') {
        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->register();
    }
    return $app;
})();
