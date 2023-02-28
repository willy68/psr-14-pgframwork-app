<?php

use Middlewares\Whoops;
use Tuupola\Http\Factory\ResponseFactory;

use function DI\add;

return [
    'doctrine.proxies.dir' => '/orm/Proxies',
    'doctrine.proxies.namespace' => 'App\Proxies',
    'doctrine.entity.namespace' => add(['App\Entity', 'App\Auth\Entity']),
    'doctrine.entity.path' => add(
        [
            dirname(__DIR__) . '/app/Entity',
            dirname(__DIR__) . '/app/Auth/Entity'
        ]
    ),
    Whoops::class => function () {
        return new Whoops(null, new ResponseFactory());
    }
];
