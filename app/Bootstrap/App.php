<?php

declare(strict_types=1);

use App\Account\AccountModule;
use App\Admin\AdminModule;
use App\Api\ApiModule;
use App\Auth\AuthModule;
use App\Blog\BlogModule;
use App\Contact\ContactModule;
use App\Demo\DemoModule;
use Application\Console\ConsoleModule;
use PgFramework\Auth\Middleware\CookieLoginMiddleware;
use PgFramework\DebugBar\EventListener\DebugBarListener;
use PgFramework\DebugBar\Middleware\DebugBarMiddleware;
use PgFramework\EventListener\ActiveRecordListener;
use PgFramework\EventListener\CsrfCookieListener;
use PgFramework\EventListener\MethodHeadListener;
use PgFramework\EventListener\MethodNotAllowedListener;
use PgFramework\EventListener\MethodOptionsListener;
use PgFramework\EventListener\PageNotFoundListener;
use PgFramework\EventListener\RecordNotFoundListener;
use PgFramework\EventListener\RendererAddGlobalListener;
use PgFramework\EventListener\RouterListener;
use PgFramework\EventListener\StringResponseListener;
use PgFramework\Middleware\ApiHeadMiddleware;
use PgFramework\Middleware\ApiOptionsMiddleware;
use PgFramework\Middleware\DispatcherMiddleware;
use PgFramework\Middleware\MethodMiddleware;
use PgFramework\Middleware\MethodNotAllowedMiddleware;
use PgFramework\Middleware\PageNotFoundMiddleware;
use PgFramework\Middleware\RendererRequestMiddleware;
use PgFramework\Middleware\RouterMiddleware;
use PgFramework\Middleware\TrailingSlashMiddleware;
use PgFramework\Security\Firewall\EventListener\RememberMeLoginListener;
use PgFramework\Security\Firewall\Firewall;
use PgFramework\Session\Listener\SessionListener;
use PgFramework\Session\Middleware\SessionMiddleware;

return [
    /* Application modules. Place your own on the list. */
    'modules' => [
        ConsoleModule::class,
        DemoModule::class,
        BlogModule::class,
        AuthModule::class,
        AdminModule::class,
        ContactModule::class,
        AccountModule::class,
        //ApiModule::class,
        //ApiClientModule::class,
    ],

    /* Other middlewares must be put on Router, RouteGroup or Route */
    'middlewares' => [
        SessionMiddleware::class,
        TrailingSlashMiddleware::class,
        RendererRequestMiddleware::class,
        MethodMiddleware::class,
        RouterMiddleware::class,
        ApiHeadMiddleware::class,
        ApiOptionsMiddleware::class,
        MethodNotAllowedMiddleware::class,
        CookieLoginMiddleware::class,
        DebugBarMiddleware::class,
        DispatcherMiddleware::class,
        PageNotFoundMiddleware::class
    ],

    'listeners' => [
        SessionListener::class,             //Request priority:   1000 Response: -1000
        RouterListener::class,              //Request priority:   900
        RendererAddGlobalListener::class,   //Request priority:   850
        MethodHeadListener::class,          //Request priority:   800
        MethodOptionsListener::class,       //Request priority:   700
        MethodNotAllowedListener::class,    //Request priority:   600
        ActiveRecordListener::class,        //Request priority:   500
        RememberMeLoginListener::class,     //Request priority:   450 Response 100
        CsrfCookieListener::class,          //Request priority:   400 Response: -100 Exception: 0
        Firewall::class,                    //Request priority:   300
        StringResponseListener::class,      //View priority:      100
        DebugBarListener::class,            //Response priority: -1000 Exception: 1000
        PageNotFoundListener::class,        //Exception priority: 500
        RecordNotFoundListener::class,      //Exception priority: 100
    ],
];
