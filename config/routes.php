<?php

use PgFramework\EventListener\BodyParserListener;
use PgFramework\Security\Firewall\EventListener\RehashPasswordListener;

use function DI\add;

// Use to add listeners for specifics routes
return [
    'routes.listeners' => add([
        [
            'path' => '^/api',
            'listeners' => [
                BodyParserListener::class,
                //ContentTypeJsonListener::class,
            ]
        ],
        [   // Use no default listeners
            'path' => '^/login',
            'methods' => ['POST'],
            // Add main LoginSuccessEvent
            'listeners' => [
                // Priority 100
                RehashPasswordListener::class
            ]
        ],
    ]),
];
