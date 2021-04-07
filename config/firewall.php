<?php

use Framework\Event\Events;
use League\Event\ListenerPriority;
use Framework\Security\Firewall\FirewallEvents;
use Framework\Security\Firewall\EventListener\ForbidenListener;
use Framework\Security\Firewall\EventListener\LoggedInListener;
use Framework\Security\Firewall\EventListener\AuthorizationListener;
use Framework\Security\Firewall\EventListener\RememberMeLoginListener;
use Framework\Security\Firewall\EventListener\RememberMeResumeListener;

return [
    'firewall.event.rules' => \DI\add([
        [
            'path' => '^/admin',
            // Other RequestMatcher rules
            //'method' => [],
            //'host' => null,
            //'schemes' => [],
            //'port' => null,
            'listeners' => [
                RememberMeLoginListener::class . '::onAuthenticationEvent' => [FirewallEvents::AUTHENTICATION, ListenerPriority::HIGH],
                LoggedInListener::class . '::onAuthenticationEvent' => [FirewallEvents::AUTHENTICATION, ListenerPriority::HIGH],

            ],
            // Events::REQUEST ne sera jamais appelé!
            'main.listeners' => [
                RememberMeResumeListener::class . '::onResponseEvent' => [Events::RESPONSE, ListenerPriority::NORMAL],
                ForbidenListener::class . '::onException' => [Events::EXCEPTION, ListenerPriority::HIGH]
            ]
        ],
        [
            'path' => '^/logout',
            // Events::REQUEST ne sera jamais appelé!
            'main.listeners' => [
                RememberMeLogoutListener::class . '::onResponseEvent' => [Events::RESPONSE, ListenerPriority::NORMAL],
            ]
        ],
        [
            'path' => '^/admin/posts/(\d+)',
            'listeners' => [
                AuthorizationListener::class . '::onAuthorization' => [FirewallEvents::AUTHORIZATION, ListenerPriority::LOW]
            ],
        ]
    ]),
    'security.voters' => \DI\add([]),
    'security.voters.rules' => \DI\add([
        [
            'path' => '^/admin/posts/(\d+)',
            // Other RequestMatcher rules
            //'method' => ['GET','POST'],
            //'host' => localhost,
            //'schemes' => ['https','http'],
            //'port' => 8000,
            'attributes' => [],
            // Events::REQUEST ne sera jamais appelé!
            'main.listeners' => []
        ],
        [
            'path' => '^/admin/categories/(\d+)',
            'attributes' => [],
            // Events::REQUEST ne sera jamais appelé!
            'main.listeners' => []
        ]
    ])
];
