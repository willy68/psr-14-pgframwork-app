<?php

use PgFramework\Security\Authorization\Voter\VoterRoles;
use PgFramework\Security\Authentication\FormAuthentication;
use PgFramework\Security\Firewall\EventListener\ForbiddenListener;
use PgFramework\Security\Firewall\EventListener\AuthorizationListener;
use PgFramework\Security\Firewall\EventListener\AuthenticationListener;
use PgFramework\Security\Firewall\EventListener\LoggedInListener;
use PgFramework\Security\Firewall\EventListener\RehashPasswordListener;
use PgFramework\Security\Firewall\EventListener\RememberMeLogoutListener;

return [
    'security.firewall.rules' => \DI\add([
        [
            // Default internal firewall dispatcher (dispatch only RequestEvent)
            'default.listeners' => [
                // Priority 100
                LoggedInListener::class,
            ],
            // Default main dispatcher (all Events except RequestEvent)
            'default.main.listeners' => [
                // Priority 100
                ForbiddenListener::class,
            ]
        ],
        [   // Use only default listeners
            'path' => '^/admin',
            // Other RequestMatcher rules
            //'method' => [],
            //'host' => null,
            //'schemes' => [],
            //'port' => null,
            // Add to internal firewall dispatcher RequestEvent
            'listeners' => [
                // Priority -100
                AuthorizationListener::class,
            ],
            // Add to main dispatcher (all Events except RequestEvent)
            //'main.listeners' => [
            //]
            'voters.rules' => [
                [
                    // Overhide main rules
                    //'path' => '^/admin/posts/(\d+)',
                    // Other RequestMatcher rules overhide main rules
                    //'methods' => ['GET','POST'],
                    //'host' => localhost,
                    //'schemes' => ['https','http'],
                    //'port' => 8000,
                    'attributes' => [
                        'ROLE_ADMIN',
                    ],
                ],
            ],
        ],
        [
            'path' => '^/mon-profil',
            'methods' => ['GET', 'POST'],
        ],
        [   // Use no default listeners
            'path' => '^/login',
            'methods' => ['POST'],
            // No default (main and internal) listener for this specific route
            'no.default.listeners' => true,
            // Add firewall RequestEvent
            'listeners' => [
                // Priority 100
                AuthenticationListener::class
            ],
            // Add main LoginSuccessEvent
            'main.listeners' => [
                // Priority 100
                RehashPasswordListener::class
            ]
        ],
        [
            // Use default internal and main listener
            'path' => '^/logout',
            // For main ResponseEvent
            'main.listeners' => [
                // Priority 100
                RememberMeLogoutListener::class,
            ]
        ],
    ]),
    // Add your authenticators here (used by AuthenticationListener)
    'security.authenticators' => \DI\add([
        \DI\get(FormAuthentication::class),
    ]),
    // Add your Voter class here (used by AuthorizationListener)
    'security.voters' => \DI\add([
        \DI\get(VoterRoles::class),
    ]),
];
