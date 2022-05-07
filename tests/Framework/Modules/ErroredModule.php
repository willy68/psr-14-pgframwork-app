<?php

namespace Tests\Framework\Modules;

use Mezzio\Router\FastRouteRouter;

class ErroredModule
{
    public function __construct(FastRouteRouter $router)
    {
        $router->get('/demo', function () {
            return new \stdClass();
        }, 'demo');
    }
}
