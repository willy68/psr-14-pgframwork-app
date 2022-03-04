<?php

use Middlewares\Whoops;
use Tuupola\Http\Factory\ResponseFactory;

return [
    'doctrine.proxies.dir' => dirname(__DIR__) . '/app/Proxies',
    'doctrine.proxies.namespace' => 'App\Proxies',
    'doctrine.entity.namespace' => \DI\add(['App\Entity']),
    'doctrine.entity.path' => \DI\add([
        dirname(__DIR__) . '/app/Entity'
    ]),
    Whoops::class => function () {
        return new Whoops(null, new ResponseFactory());
    }
];
