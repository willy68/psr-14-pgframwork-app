<?php

use App\Auth\Listener\RehashPasswordListener;
use PgFramework\Security\Authorization\Voter\VoterRoles;
use PgFramework\Security\Authentication\FormAuthentication;
use PgFramework\Security\Firewall\EventListener\ForbiddenListener;
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
                ForbiddenListener::class,
                RememberMeLoginListener::class,
            ]
        ],
        [   // Use only default listeners
            'path' => '^/admin',
            // Other RequestMatcher rules
            //'method' => [],
            //'host' => null,
            //'schemes' => [],
            //'port' => null,
            'listeners' => [
                AuthorizationListener::class,
            ],
            //'main.listeners' => [
            //]
            'voters.rules' => [
                [
                    // Overhide main rules
                    'path' => '^/admin/posts/(\d+)',
                    // Other RequestMatcher rules overhide main rules
                    //'method' => ['GET','POST'],
                    //'host' => localhost,
                    //'schemes' => ['https','http'],
                    //'port' => 8000,
                    'attributes' => [
                        'ROLE_ADMIN',
                    ],
                ],
            ],
        ],
        [   // Use no default listeners
            'path' => '^/login',
            'method' => ['POST'],
            // No default listener for this specific route
            'no.default.listeners' => true,
            // For Firewall RequestEvent
            'listeners' => [
                AuthenticationListener::class
            ],
            // For LoginSuccessEvent
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
    // Add your authenticators here
    'security.authenticators' => \DI\add([
        \DI\get(FormAuthentication::class),
    ]),
    // Add your Voter class here
    'security.voters' => \DI\add([
        \DI\get(VoterRoles::class),
    ]),
];
