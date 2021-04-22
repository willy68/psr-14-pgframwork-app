<?php

use PgFramework\EventListener\BodyParserListener;
use PgFramework\EventListener\ContentTypeJsonListener;

// Use to add listeners for specifics routes
return [
    'routes.listeners' => \DI\add([
        [
            'path' => '^/api',
            'listeners' => [
                BodyParserListener::class,
                ContentTypeJsonListener::class,
            ]
        ],
    ]),
];
