<?php

declare(strict_types=1);

use App\Api\ApiModule;
use App\Auth\AuthModule;
use App\Blog\BlogModule;
use App\Demo\DemoModule;
use App\Admin\AdminModule;
use App\Api\ApiClientModule;
use Application\Console\ConsoleModule;
use PgFramework\DebugBar\EventListener\DebugBarListener;
use PgFramework\DebugBar\Middleware\DebugBarMiddleware;
use PgFramework\Security\Firewall\Firewall;
use PgFramework\Middleware\MethodMiddleware;
use PgFramework\Middleware\RouterMiddleware;
use PgFramework\EventListener\RouterListener;
use PgFramework\Middleware\ApiHeadMiddleware;
use PgFramework\Middleware\ApiOptionsMiddleware;
use PgFramework\EventListener\CsrfCookieListener;
use PgFramework\EventListener\MethodHeadListener;
use PgFramework\EventListener\ActiveRecordListener;
use PgFramework\EventListener\CallableResolverListener;
use PgFramework\EventListener\PageNotFoundListener;
use PgFramework\Middleware\TrailingSlashMiddleware;
use PgFramework\EventListener\MethodOptionsListener;
use PgFramework\EventListener\RecordNotFoundListener;
use PgFramework\EventListener\StringResponseListener;
use PgFramework\Middleware\MethodNotAllowedMiddleware;
use PgFramework\EventListener\MethodNotAllowedListener;
use PgFramework\EventListener\ParamsResolverListener;

return [
    /* Application modules. Place your own on the list */
    'modules' => [
        ConsoleModule::class,
        DemoModule::class,
        AdminModule::class,
        BlogModule::class,
        AuthModule::class,
        //ApiModule::class,
        //ApiClientModule::class,
    ],

    /* Other middlewares must be put on Router, RouteGroup or Route */
    'middlewares' => [
        TrailingSlashMiddleware::class,
        MethodMiddleware::class,
        RouterMiddleware::class,
        ApiHeadMiddleware::class,
        ApiOptionsMiddleware::class,
        MethodNotAllowedMiddleware::class,
        DebugBarMiddleware::class,
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
        CallableResolverListener::class,
        ParamsResolverListener::class,
        StringResponseListener::class,
        DebugBarListener::class,
        RecordNotFoundListener::class,
    ],
];
