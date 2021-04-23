<?php

use App\Api\ApiModule;
use App\Auth\AuthModule;
use App\Blog\BlogModule;
use App\Demo\DemoModule;
use App\Admin\AdminModule;
use App\Api\ApiClientModule;
use PgFramework\Security\Firewall\Firewall;
use PgFramework\Middleware\MethodMiddleware;
use PgFramework\Middleware\RouterMiddleware;
use PgFramework\EventListener\RouterListener;
use PgFramework\Middleware\ApiHeadMiddleware;
use PgFramework\Middleware\ApiOptionsMiddleware;
use PgFramework\Middleware\DispatcherMiddleware;
use PgFramework\EventListener\CsrfCookieListener;
use PgFramework\EventListener\MethodHeadListener;
use PgFramework\Middleware\PageNotFoundMiddleware;
use PgFramework\EventListener\ActiveRecordListener;
use PgFramework\EventListener\PageNotFoundListener;
use PgFramework\Middleware\TrailingSlashMiddleware;
use PgFramework\EventListener\MethodOptionsListener;
use PgFramework\EventListener\RecordNotFoundListener;
use PgFramework\EventListener\StringResponseListener;
use PgFramework\Middleware\MethodNotAllowedMiddleware;
use PgFramework\EventListener\MethodNotAllowedListener;

return [
    /* Application modules. Place your own on the list */
    'modules' => [
        DemoModule::class,
        AdminModule::class,
        BlogModule::class,
        AuthModule::class,
        ApiModule::class,
        ApiClientModule::class,
    ],

    /* Base middlewares PageNotFound must be the last.
       Other middlewares must be put on Router, RouteGroup or Route */
    'middlewares' => [
        TrailingSlashMiddleware::class,
        MethodMiddleware::class,
        RouterMiddleware::class,
        ApiHeadMiddleware::class,
        ApiOptionsMiddleware::class,
        MethodNotAllowedMiddleware::class,
        DispatcherMiddleware::class,
        PageNotFoundMiddleware::class,
    ],

    'listeners' => [
        RouterListener::class,
        MethodHeadListener::class,
        MethodOptionsListener::class,
        MethodNotAllowedListener::class,
        PageNotFoundListener::class,
        ActiveRecordListener::class,
        CsrfCookieListener::class,
        Firewall::class,
        StringResponseListener::class,
        RecordNotFoundListener::class,
    ],

    /* DI Base configuration. Place your own on the list */
    'config' => [
        dirname(dirname(__DIR__)) . '/config/config.php',
        dirname(dirname(__DIR__)) . '/config/firewall.php',
        dirname(dirname(__DIR__)) . '/config/routes.php',
        dirname(dirname(__DIR__)) . '/config/router.php',
        dirname(dirname(__DIR__)) . '/config/database.php',
        dirname(dirname(__DIR__)) . '/config/twig.php',
    ]
];
