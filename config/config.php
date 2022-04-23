<?php

use Middlewares\Whoops;
use Tuupola\Http\Factory\ResponseFactory;

return [
    'doctrine.proxies.dir' => \DI\get('app.cache.dir') . '/orm/Proxies',
    'doctrine.proxies.namespace' => 'App\Proxies',
    'doctrine.entity.namespace' => \DI\add(['App\Entity', 'App\Auth\Entity']),
    'doctrine.entity.path' => \DI\add(
        [
            dirname(__DIR__) . '/app/Entity',
            dirname(__DIR__) . '/app/Auth/Entity'
        ]
    ),
    Whoops::class => function () {
        return new Whoops(null, new ResponseFactory());
    }
];
