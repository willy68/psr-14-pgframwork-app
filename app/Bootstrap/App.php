<?php

declare(strict_types=1);

use App\Api\ApiModule;
use App\Auth\AuthModule;
use App\Blog\BlogModule;
use App\Demo\DemoModule;
use App\Admin\AdminModule;
use App\Api\ApiClientModule;
use App\Contact\ContactModule;
use Application\Console\ConsoleModule;
use PgFramework\Security\Firewall\Firewall;
use PgFramework\Middleware\MethodMiddleware;
use PgFramework\Middleware\RouterMiddleware;
use PgFramework\EventListener\RouterListener;
use PgFramework\Middleware\ApiHeadMiddleware;
use PgFramework\Middleware\ApiOptionsMiddleware;
use PgFramework\Middleware\DispatcherMiddleware;
use PgFramework\EventListener\CsrfCookieListener;
use PgFramework\EventListener\MethodHeadListener;
use PgFramework\Session\Listener\SessionListener;
use PgFramework\Middleware\PageNotFoundMiddleware;
use PgFramework\EventListener\ActiveRecordListener;
use PgFramework\EventListener\PageNotFoundListener;
use PgFramework\Middleware\TrailingSlashMiddleware;
use PgFramework\EventListener\MethodOptionsListener;
use PgFramework\EventListener\RecordNotFoundListener;
use PgFramework\EventListener\StringResponseListener;
use PgFramework\Session\Middleware\SessionMiddleware;
use PgFramework\Middleware\MethodNotAllowedMiddleware;
use PgFramework\DebugBar\Middleware\DebugBarMiddleware;
use PgFramework\EventListener\MethodNotAllowedListener;
use PgFramework\DebugBar\EventListener\DebugBarListener;

return [
    /* Application modules. Place your own on the list */
    'modules' => [
        ConsoleModule::class,
        DemoModule::class,
        AdminModule::class,
        BlogModule::class,
        ContactModule::class,
        AuthModule::class,
        //ApiModule::class,
        //ApiClientModule::class,
    ],

    /* Other middlewares must be put on Router, RouteGroup or Route */
    'middlewares' => [
        SessionMiddleware::class,
        TrailingSlashMiddleware::class,
        MethodMiddleware::class,
        RouterMiddleware::class,
        ApiHeadMiddleware::class,
        ApiOptionsMiddleware::class,
        MethodNotAllowedMiddleware::class,
        DebugBarMiddleware::class,
        DispatcherMiddleware::class,
        PageNotFoundMiddleware::class
    ],

    'listeners' => [
        SessionListener::class,             //Request priority:   1000 Response: -1000
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
