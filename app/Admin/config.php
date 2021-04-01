<?php

use App\Admin\AdminModule;
use Framework\Event\Events;
use App\Admin\DashboardAction;
use App\Admin\AdminTwigExtension;
use League\Event\ListenerPriority;

return [
    'admin.prefix' => '/admin',
    'admin.widgets' => [],
    AdminTwigExtension::class => \DI\create()->constructor(\DI\get('admin.widgets')),
    AdminModule::class => \DI\autowire()
        ->constructorParameter('prefix', \DI\get('admin.prefix')),
    DashboardAction::class => \DI\autowire()
        ->constructorParameter('widgets', \DI\get('admin.widgets')),
    'firewall.event.rules' => \DI\add([
        [
            [
                'path' => \DI\get('admin.prefix'),
                'route.name' => null,
                'listeners' => [
                    'CookieLoggingListener::class' => [Events::REQUEST, ListenerPriority::HIGH],
                    'LoggedInListener::class' => [Events::REQUEST, ListenerPriority::HIGH],
                    'ForbiddenListener::class' => [Events::REQUEST, ListenerPriority::HIGH]
                ]
            ]
        ]
    ])
];
