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
        RouterListener::class,              //Request priority:   900
        MethodHeadListener::class,          //Request priority:   800
        MethodOptionsListener::class,       //Request priority:   700
        MethodNotAllowedListener::class,    //Request priority:   600
        PageNotFoundListener::class,        //Exception priority: 500
        ActiveRecordListener::class,        //Request priority:   500
        CsrfCookieListener::class,          //Request priority:   400 Response: -100 Exception: 0
        Firewall::class,                    //Request priority:   300
        StringResponseListener::class,      //View priority:      100
        DebugBarListener::class,            //Response priority: -1000
        RecordNotFoundListener::class,      //Exception priority: 100
    ],
];
