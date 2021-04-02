<?php

use App\Admin\AdminModule;
use Framework\Event\Events;
use App\Admin\DashboardAction;
use App\Admin\AdminTwigExtension;
use League\Event\ListenerPriority;
use Framework\Security\Firewall\FirewallEvents;
use Framework\Security\Firewall\EventListener\ForbidenListener;
use Framework\Security\Firewall\EventListener\LoggedInListener;
use Framework\Security\Firewall\EventListener\RememberMeLoginListener;
use Framework\Security\Firewall\EventListener\RememberMeResumeListener;

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
            'path' => '^/admin',
            'listeners' => [
                RememberMeLoginListener::class . '::onAuthenticationEvent' => [FirewallEvents::AUTHENTICATION, ListenerPriority::HIGH],
                LoggedInListener::class . '::onAuthenticationEvent' => [FirewallEvents::AUTHENTICATION, ListenerPriority::HIGH],

            ],
            // Events::REQUEST ne sera jamais appelé!
            'main.listeners' => [
                RememberMeResumeListener::class . '::onResponseEvent' => [Events::RESPONSE, ListenerPriority::NORMAL],
                ForbidenListener::class . '::onException' => [Events::EXCEPTION, ListenerPriority::HIGH]
            ]
        ]
    ])
];
