<?php

use PgFramework\EventListener\BodyParserListener;
use PgFramework\Security\Firewall\EventListener\ForbiddenListener;
use PgFramework\Security\Firewall\EventListener\RehashPasswordListener;

use function DI\add;

// Use to add listeners for specifics routes
return [
    'routes.listeners' => add([
        [
            'path' => '^/api',
            'listeners' => [
                BodyParserListener::class,
                ForbiddenListener::class,
            ]
        ],
        [
            'path' => '^/login',
            'methods' => ['POST'],
            'listeners' => [
                BodyParserListener::class,
                // Priority 100
                RehashPasswordListener::class
            ]
        ],
    ]),
];
