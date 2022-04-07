<?php

use App\Auth\Listener\RehashPasswordListener;
use PgFramework\Security\Authorization\Voter\VoterRoles;
use PgFramework\Security\Authentication\FormAuthentication;
use PgFramework\Security\Firewall\EventListener\ForbidenListener;
use PgFramework\Security\Firewall\EventListener\AuthorizationListener;
use PgFramework\Security\Firewall\EventListener\AuthenticationListener;
use PgFramework\Security\Firewall\EventListener\RememberMeLoginListener;
use PgFramework\Security\Firewall\EventListener\RememberMeLogoutListener;

return [
    'security.firewall.rules' => \DI\add([
        [
            'default.listeners' => [
                RememberMeLoginListener::class,
            ],
            'default.main.listeners' => [
                ForbidenListener::class,
                RememberMeLoginListener::class,
            ]
        ],
        [ // Use default listeners and specific voters rules
            'path' => '^/admin/posts/(\d+)',
            'listeners' => [
                AuthorizationListener::class,
            ]
        ],
        [ // Use only default listeners
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
        [ // Use no default listeners
            'path' => '^/login',
            // Other RequestMatcher rules
            'method' => ['POST'],
            // No default listener for this specific route
            'no.default.listeners' => true,
            // For Request
            'listeners' => [
                AuthenticationListener::class
            ],
            'main.listeners' => [
               RehashPasswordListener::class
            ]
        ],
        [
            'path' => '^/logout',
            'main.listeners' => [
                RememberMeLogoutListener::class,
            ]
        ],
    ]),
    'security.authenticators' => \DI\add([
        \DI\get(FormAuthentication::class),
    ]),
    'security.authorization.listeners' => \DI\add([
        AuthorizationListener::class,
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
