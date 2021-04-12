<?php

use PgFramework\Event\Events;
use PgFramework\Security\Authorization\Voter\VoterRoles;
use League\Event\ListenerPriority;
use PgFramework\Security\Firewall\FirewallEvents;
use PgFramework\Security\Firewall\EventListener\ForbidenListener;
use PgFramework\Security\Firewall\EventListener\LoggedInListener;
use PgFramework\Security\Firewall\EventListener\AuthorizationListener;
use PgFramework\Security\Firewall\EventListener\RememberMeLoginListener;
use PgFramework\Security\Firewall\EventListener\RememberMeLogoutListener;
use PgFramework\Security\Firewall\EventListener\RememberMeResumeListener;

return [
    'firewall.event.rules' => \DI\add([
        [
            'path' => '^/admin/posts/(\d+)',
            'listeners' => [
                RememberMeLoginListener::class . '::onAuthenticationEvent' => [FirewallEvents::AUTHENTICATION, ListenerPriority::HIGH],
                LoggedInListener::class . '::onAuthenticationEvent' => [FirewallEvents::AUTHENTICATION, ListenerPriority::HIGH],
                AuthorizationListener::class . '::onAuthorization' => [FirewallEvents::AUTHORIZATION, ListenerPriority::LOW]
            ],
            // Events::REQUEST ne sera jamais appelé!
            'main.listeners' => [
                RememberMeResumeListener::class . '::onResponseEvent' => [Events::RESPONSE, ListenerPriority::NORMAL],
                ForbidenListener::class . '::onException' => [Events::EXCEPTION, ListenerPriority::HIGH]
            ]
        ],
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
    ]),
    'security.voters' => \DI\add([
        \DI\get(VoterRoles::class),
    ]),
    'security.voters.rules' => \DI\add([
        [
            'path' => '^/admin/posts/(\d+)',
            // Other RequestMatcher rules
            //'method' => ['GET','POST'],
            //'host' => localhost,
            //'schemes' => ['https','http'],
            //'port' => 8000,
            'attributes' => [
                'ROLE_ADMIN',
            ],
        ],
        [
            'path' => '^/admin/categories/(\d+)',
            'attributes' => [],
        ]
    ])
];
