<?php

/**
 * Add your own Twig extensions inside Array
 * use \DI\get
 * ex: \DI\get(MyExtension::class)
 */
return [
    'views.path' => dirname(__DIR__) . '/app/views',
    'twig.entrypoints' => dirname(__DIR__) . '/public/assets/js',
    'twig.extensions' => \DI\add([
        /** Add your extensions here */

    ])
];
