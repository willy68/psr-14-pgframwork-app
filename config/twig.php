<?php

/**
 * Add your own Twig extensions inside Array
 * ex: \DI\get(MyExtension::class)
 */

use function DI\add;

return [
    'views.path' => dirname(__DIR__) . '/app/views',
    'twig.entrypoints' => dirname(__DIR__) . '/public/assets/js/entrypoints.json',
    'twig.extensions' => add([
        /** Add your extensions here */

    ])
];
