<?php

namespace Tests\Framework\Modules;

use PgRouter\RouteCollector;

class StringModule
{
    public function __construct(RouteCollector $router)
    {
        $router->get('/demo', function () {
            return 'DEMO';
        }, 'demo');
    }
}
