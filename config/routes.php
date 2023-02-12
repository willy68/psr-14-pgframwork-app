<?php

use PgFramework\EventListener\BodyParserListener;
use PgFramework\EventListener\ContentTypeJsonListener;
use PgFramework\Security\Firewall\EventListener\RehashPasswordListener;

// Use to add listeners for specifics routes
return [
    'routes.listeners' => \DI\add([
        /*[
            'path' => '^/api',
            'listeners' => [
                BodyParserListener::class,
                ContentTypeJsonListener::class,
            ]
        ],*/
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
