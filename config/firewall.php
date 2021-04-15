<?php

use PgFramework\Event\Events;
use League\Event\ListenerPriority;
use PgFramework\EventListener\BodyParserListener;
use PgFramework\EventListener\ContentTypeJsonListener;
use PgFramework\Security\Authorization\Voter\VoterRoles;
use PgFramework\Security\Firewall\EventListener\ForbidenListener;
use PgFramework\Security\Firewall\EventListener\AuthorizationListener;
use PgFramework\Security\Firewall\EventListener\RememberMeLoginListener;
use PgFramework\Security\Firewall\EventListener\RememberMeLogoutListener;

return [
    'security.firewall.rules' => \DI\add([
        [
            'default.listeners' => [
                RememberMeLoginListener::class . '::onAuthentication' => [Events::REQUEST, ListenerPriority::HIGH],
            ],
            'default.main.listeners' => [
                ForbidenListener::class . '::onException' => [Events::EXCEPTION, ListenerPriority::HIGH],
                RememberMeLoginListener::class . '::onResponse' => [Events::RESPONSE, ListenerPriority::NORMAL],
            ]
        ],
        [
            'path' => '^/admin/posts/(\d+)',
            'listeners' => [
                AuthorizationListener::class . '::onAuthorization' => [Events::REQUEST, ListenerPriority::LOW],
            ]
        ],
        [
            'path' => '^/api',
            'no.default.listeners' => true,
            'listeners' => [
                //AuthorizationListener::class . '::onAuthorization' => [Events::REQUEST, ListenerPriority::NORMAL],
                BodyParserListener::class => [Events::REQUEST, ListenerPriority::LOW],
            ],
            'main.listeners' => [
                ContentTypeJsonListener::class => [Events::RESPONSE, ListenerPriority::LOW],
            ]
        ],
        [
            'path' => '^/admin',
            // Other RequestMatcher rules
            //'method' => [],
            //'host' => null,
            //'schemes' => [],
            //'port' => null,
            //'listeners' => [
            //],
            //'main.listeners' => [
            //]
        ],
        [
            'path' => '^/logout',
            // Events::REQUEST ne sera jamais appelé!
            'main.listeners' => [
                RememberMeLogoutListener::class . '::onResponse' => [Events::RESPONSE, ListenerPriority::NORMAL],
            ]
        ],
    ]),
    'security.authorization.listeners' => \DI\add([
        AuthorizationListener::class . '::onAuthorization' => [Events::REQUEST, ListenerPriority::LOW],
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
        ],
    ])
];
