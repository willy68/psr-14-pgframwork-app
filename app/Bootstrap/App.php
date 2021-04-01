<?php

use App\Api\ApiModule;
use App\Auth\AuthModule;
use App\Blog\BlogModule;
use App\Demo\DemoModule;
use App\Admin\AdminModule;
use Framework\Event\Events;
use App\Api\ApiClientModule;
use League\Event\ListenerPriority;
use Framework\EventListener\CsrfListener;
use Framework\Middleware\MethodMiddleware;
use Framework\Middleware\RouterMiddleware;
use Framework\EventListener\RouterListener;
use Framework\Middleware\ApiHeadMiddleware;
use Framework\Middleware\ApiOptionsMiddleware;
use Framework\Middleware\DispatcherMiddleware;
use Framework\EventListener\CsrfCookieListener;
use Framework\Middleware\PageNotFoundMiddleware;
use Framework\EventListener\ActiveRecordListener;
use Framework\EventListener\InvalidCsrfListener;
use Framework\EventListener\PageNotFoundListener;
use Framework\Middleware\TrailingSlashMiddleware;
use Framework\Middleware\MethodNotAllowedMiddleware;
use Framework\EventListener\MethodNotAllowedListener;
use Framework\EventListener\StringResponseListener;

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
        RouterListener::class => [Events::REQUEST, ListenerPriority::HIGH],
        MethodNotAllowedListener::class => [Events::REQUEST, ListenerPriority::HIGH],
        PageNotFoundListener::class => [Events::REQUEST, ListenerPriority::HIGH],
        ActiveRecordListener::class => [Events::REQUEST, ListenerPriority::HIGH],
        //CsrfCookieListener::class . "::onRequestEvent" => [Events::REQUEST, ListenerPriority::HIGH],
        //CsrfCookieListener::class . "::onResponseEvent" => [Events::RESPONSE, ListenerPriority::HIGH]
        CsrfListener::class . "::onRequestEvent" => [Events::REQUEST, ListenerPriority::HIGH],
        InvalidCsrfListener::class . "::onException" => [Events::EXCEPTION, ListenerPriority::HIGH],
        StringResponseListener::class . "::onView" => [Events::VIEW, ListenerPriority::HIGH],
    ],

    /* DI Base configuration. Place your own on the list */
    'config' => [
        dirname(dirname(__DIR__)) . '/config/config.php',
        dirname(dirname(__DIR__)) . '/config/router.php',
        dirname(dirname(__DIR__)) . '/config/database.php',
        dirname(dirname(__DIR__)) . '/config/twig.php',
    ]
];
