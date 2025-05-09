<?php

namespace Tests\Framework\Modules;

use PgRouter\RouteCollector;

class ErroredModule
{
    public function __construct(RouteCollector $router)
    {
        $router->get('/demo', function () {
            return new \stdClass();
        }, 'demo');
    }
}
